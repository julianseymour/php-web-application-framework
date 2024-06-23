<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\validate\AjaxValidatorInterface;
use JulianSeymour\PHPWebApplicationFramework\validate\UniqueValidator;

class RegistrationEmailAddressValidator extends UniqueValidator implements AjaxValidatorInterface{

	public function __construct(){
		parent::__construct(config()->getNormalUserClass(), 's', "normalizedEmailAddress");
		$this->setSpecialFailureStatus(ERROR_INVALID_EMAIL_ADDRESS);
	}

	public function getSuccessCommand(){
		$element = new GetElementByIdCommand(getInputParameter("id"));
		$set_attribute = new SetAttributeCommand($element, [
			"validity" => "valid"
		]);
		if(getInputParameter('form') === RegistrationForm::getFormDispatchIdStatic()){
			$notice = new GetElementByIdCommand("register_notice");
			$set_inner = new SetInnerHTMLCommand($notice, "");
			$set_attribute->pushSubcommand($set_inner);
		}
		return $set_attribute;
	}

	public function getFailureCommand(){
		$element = new GetElementByIdCommand(getInputParameter("id"));
		$set_attribute = new SetAttributeCommand($element, [
			"validity" => "invalid"
		]);
		if(getInputParameter('form') === RegistrationForm::getFormDispatchIdStatic()){
			$notice = new GetElementByIdCommand("register_notice");
			$err = ErrorMessage::getResultMessage($this->getObjectStatus());
			$set_inner = new SetInnerHTMLCommand($notice, $err);
			$set_attribute->pushSubcommand($set_inner);
		}
		return $set_attribute;
	}

	protected function prevalidate(&$arr){
		$f = __METHOD__;
		
		if(!array_key_exists('emailAddress', $arr)){
			Debug::warning("{$f} email address was not posted");
			Debug::printArray($arr);
			Debug::print("{$f} about to print input parameters");
			Debug::printArray(getInputParameters());
			Debug::printStackTraceNoExit();
		}
		$this->setParameters([
			EmailAddressDatum::normalize($arr['emailAddress'])
		]);
		return SUCCESS;
	}
}
