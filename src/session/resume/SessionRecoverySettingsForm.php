<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\session\AbstractSessionForm;
use Exception;

class SessionRecoverySettingsForm extends AbstractSessionForm{

	public static function getFormDispatchIdStatic(): ?string{
		return "resume_session";
	}

	public function getDirectives(): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$context = $this->getContext();
			if(is_array($context->getRecoveryKit())){
				if($print){
					Debug::print("{$f} recovery kit decrypted as array");
				}
				$arr = [
					DIRECTIVE_DELETE
				];
			}else{
				if($print){
					Debug::print("{$f} context is uninitialized, or recovery kit was not decrypted as an array");
				}
				$arr = [
					DIRECTIVE_INSERT
				];
			}
			return $arr;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getFormDataIndices(): ?array{
		$f = __METHOD__;
		if(!$this->hasContext()){
			Debug::error("{$f} context is undefined");
		}
		$context = $this->getContext();
		if(is_array($context->getRecoveryKit())){
			return [];
		}
		return parent::getFormDataIndices();
	}

	protected function getUserAgentHint(){
		return _("With this option selected, your session cookies will only be valid on the same browser used to create the cookie");
	}

	protected function getIpAddressHint(){
		return _("Select this for additional security if you know your IP address will not change");
	}

	public static function getExpandingMenuLabelString($context){
		return _("Stay logged in");
	}

	public static function getExpandingMenuRadioButtonIdAttribute(){
		return "radio_settings_session";
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("resume_session_form");
	}

	public static function getMassDeletionIndices($context = null){
		return [
			"userKeyHash"
		];
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		try{
			switch($name){
				case DIRECTIVE_DELETE:
					$button = $this->generateGenericButton($name);
					$innerHTML = _("Forget me");
					$button->setInnerHTML($innerHTML);
					break;
				case DIRECTIVE_INSERT:
					$button = $this->generateGenericButton($name);
					$innerHTML = _("Remember me");
					$button->setInnerHTML($innerHTML);
					break;
				case DIRECTIVE_MASS_DELETE:
					$button = $this->generateGenericButton($name, "userKeyHash");
					$innerHTML = _("Delete all saved sessions");
					$button->setInnerHTML($innerHTML);
					break;
				default:
					Debug::error("{$f} invalid name attribute \"{$name}\"");
					return null;
			}
			$button->setStyleProperties(["margin-top" => "1rem"]);
			return [
				$button
			];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function getFormHeaderString(){
		$context = $this->getContext();
		if(is_array($context->getRecoveryKit())){
			return _("Click 'Forget Me' to delete all resumable sessions.");
		}
		return _("By choosing 'Remember Me', your session will automatically refresh upon expiration. You will remain logged in until you logout, terminate your saved session or delete this site's cookies from your device.");
	}
}

