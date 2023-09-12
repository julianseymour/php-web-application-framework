<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\honeypot;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\is_json;
use function JulianSeymour\PHPWebApplicationFramework\setInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use Exception;

class HoneypotValidator extends Validator
{

	protected $formClass;

	public function setFormClass($class)
	{
		$f = __METHOD__; //HoneypotValidator::getShortClass()."(".static::getShortClass().")->setFormClass({$class})";
		if(! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}elseif(!is_a($class, FormElement::class, true)) {
			Debug::error("{$f} class is not a form");
		}
		return $this->formClass = $class;
	}

	public function __construct($form_class)
	{
		parent::__construct();
		if(!empty($form_class)) {
			$this->setFormClass($form_class);
		}
	}

	public function hasFormClass()
	{
		return isset($this->formClass) && class_exists($this->formClass);
	}

	public function getFormClass()
	{
		$f = __METHOD__; //HoneypotValidator::getShortClass()."(".static::getShortClass().")->getFormClass()";
		if(!$this->hasFormClass()) {
			Debug::error("{$f} form class is undefined");
		}
		return $this->formClass;
	}

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //HoneypotValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		try{
			$honeypots = $this->getFormClass()::getHoneypotCountArray();
			if(empty($honeypots)) {
				return SUCCESS;
			}
			$cskp = app()->acquireCurrentServerKeypair(db()->getConnection(PublicReadCredentials::class));
			$pot_keys = array_keys($honeypots);
			for ($i = 0; $i < count($pot_keys); $i ++) {
				$decoy_count = $honeypots[$pot_keys[$i]];
				$pot_num = $i + 1;
				$index = "__pot{$pot_num}";
				if(! hasInputParameter($index)) {
					Debug::printPost("{$f} input parameter \"{$index}\" is undefined");
				}
				$solution = base64_decode(getInputParameter($index));
				$decrypted = $cskp->decrypt($solution);
				if(!is_json($decrypted)) {
					static::debugError("{$f} \"{$decrypted}\" is not JSON");
				}
				$json_parsed = json_decode($decrypted, true);
				// Debug::print($json_parsed);
				$nonce = base64_decode($json_parsed['nonce_64']);
				$new_name = $json_parsed["new_name"];
				$name = $json_parsed["name"];
				// Debug::print("{$f} legit input name is \"{$new_name}\"");
				$sign_me = "{$nonce}:{$new_name}:{$name}";
				$signature = base64_decode($json_parsed['signature_64']);
				if(!$cskp->verifySignedMessage($signature, $sign_me)) {
					Debug::warning("{$f} honeypot signature failed");
					return ERROR_TAMPER_POST;
				} else
					for ($j = 0; $j < $decoy_count; $j ++) {
						$decoy_name = Hunnypot::generateDecoyNameAttribute($nonce, $j);
						if(hasInputParameter($decoy_name)) {
							$dn = getInputParameter($decoy_name);
							// Debug::print("{$f} user filled in a honeypot at index \"{$decoy_name}\" with \"{$dn}\"");
							return ERROR_HONEYPOT;
						}
					}
				// Debug::print("{$f} user did not fill in any honeypots imitating input \"{$name}\"");
				if(hasInputParameter($new_name)) {
					// Debug::print("{$f} filling in POST[{$name}]");
					setInputParameter($name, getInputParameter($new_name));
					$validate_me[$name] = $validate_me[$new_name];
				}
				setInputParameter($new_name, null);
			}
			// Debug::print("{$f} all clear");
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->formClass);
	}
}
