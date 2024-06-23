<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\honeypot;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\style\selector\AttributeSelector;
use JulianSeymour\PHPWebApplicationFramework\style\selector\ElementSelector;
use JulianSeymour\PHPWebApplicationFramework\style\selector\NegationSelector;
use Exception;

class Hunnypot extends HiddenInput
{

	protected $realInput;

	protected $potNumber;

	protected $decoyCount;

	protected $nonce;

	public function __construct($input = null)
	{
		parent::__construct();
		if(isset($input)){
			$this->setRealInput($input);
		}
	}

	public function getDecoyCount()
	{
		return $this->decoyCount;
	}

	public function setDecoyCount($count)
	{
		return $this->decoyCount = $count;
	}

	public static function generateDecoyNameAttribute($nonce, $num)
	{
		return sha1($nonce . $num);
	}

	public function hasRealInput()
	{
		return isset($this->realInput);
	}

	public function getNonce()
	{
		return $this->nonce;
	}

	public function setNonce($nonce)
	{
		return $this->nonce = $nonce;
	}

	public static function getSelector($pot_id, $new_name)
	{
		$selector = new ElementSelector("input");
		$selector->pushCoselector(new AttributeSelector("honey", "delicious"), new AttributeSelector("pot", $pot_id), new NegationSelector(new AttributeSelector("name", $new_name)));
		return $selector;
	}

	public function setRealInput($input)
	{
		$f = __METHOD__; //Hunnypot::getShortClass()."(".static::getShortClass().")->setRealInput()";
		try{
			$form = $input->getForm();
			$cskp = app()->acquireCurrentServerKeypair(db()->getConnection(PublicReadCredentials::class));
			if($cskp == null){
				Debug::warning("{$f} current server keypair returned null");
				return null;
			}
			$form->pushHoneypot($this);
			$new_name = sha1(random_bytes(32));
			$name = $input->getNameAttribute();
			$num = $this->getPotNumber();
			// input[honey=\"delicious\"][pot=\"{$num}\"]:not([name=\"{$new_name}\"])
			$input->setAttribute("honey", "delicious");
			$form_id = $form->getIdAttribute();
			if($form_id === "user_new"){
				Debug::error("{$f} invalid form ID");
			}
			$pot_id = "{$form_id}_{$num}";
			$input->setAttribute("pot", $pot_id);
			$style = $form->getHoneypotStyleElement();
			$selector = static::getSelector($pot_id, $new_name);
			$rule = $style->getChildNodeNumber(0);
			$rule->pushSelector($selector);
			$nonce = $this->setNonce(random_bytes(32)); // used for deterministically generating the decoy input names
			$sign_me = "{$nonce}:{$new_name}:{$name}";
			$this->setNameAttribute("__pot{$num}");
			$this->setValueAttribute(base64_encode($cskp->encrypt(json_encode([
				'name' => $name,
				'new_name' => $new_name,
				'nonce_64' => base64_encode($nonce),
				'signature_64' => base64_encode($cskp->signMessage($sign_me))
			]))));
			$input->setHoneypot($this);
			$input->setNameAttribute($new_name);
			// Debug::print("{$f} returning normally");
			return $this->realInput = $input;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setPotNumber($num)
	{
		$this->potNumber = $num;
		return $this->getPotNumber();
	}

	public function getPotNumber()
	{
		return $this->potNumber;
	}

	public function hasPotNumber()
	{
		return isset($this->potNumber);
	}
}
