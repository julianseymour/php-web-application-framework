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

class RegistrationEmailAddressValidator extends UniqueValidator implements AjaxValidatorInterface
{

	public function __construct()
	{
		parent::__construct(config()->getNormalUserClass(), 's', "normalizedEmailAddress");
		$this->setSpecialFailureStatus(ERROR_INVALID_EMAIL_ADDRESS);
	}

	public function getSuccessCommand()
	{
		$element = new GetElementByIdCommand(getInputParameter("id"));
		$notice = new GetElementByIdCommand("register_notice");
		$command = (new SetAttributeCommand($element, [
			"validity" => "valid"
		]));
		if (getInputParameter('form') === RegistrationForm::getFormDispatchIdStatic()) {
			$command->pushSubcommand(new SetInnerHTMLCommand($notice, ""));
		}
		return $command;
	}

	public function getFailureCommand()
	{
		$element = new GetElementByIdCommand(getInputParameter("id"));
		$notice = new GetElementByIdCommand("register_notice");
		$command = (new SetAttributeCommand($element, [
			"validity" => "invalid"
		]));
		if (getInputParameter('form') === RegistrationForm::getFormDispatchIdStatic()) {
			$command->pushSubcommand(new SetInnerHTMLCommand($notice, ErrorMessage::getResultMessage($this->getObjectStatus())));
		}
		return $command;
	}

	protected function prevalidate(&$arr)
	{
		$f = __METHOD__; //RegistrationEmailAddressValidator::getShortClass()."(".static::getShortClass().")->prevalidate()";
		if (! array_key_exists('emailAddress', $arr)) {
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
