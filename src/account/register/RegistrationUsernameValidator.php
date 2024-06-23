<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UsernameData;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\validate\AjaxValidatorInterface;
use JulianSeymour\PHPWebApplicationFramework\validate\UniqueValidator;
use Exception;

class RegistrationUsernameValidator extends UniqueValidator implements AjaxValidatorInterface
{

	public function __construct()
	{
		parent::__construct(UsernameData::class, 's', "name");
		$this->setSpecialFailureStatus(ERROR_INVALID_USERNAME);
	}

	public function getSuccessCommand()
	{
		$element = new GetElementByIdCommand(getInputParameter("id"));
		$notice = new GetElementByIdCommand("register_notice");
		return (new SetAttributeCommand($element, [
			"validity" => "valid"
		]))->withSubcommands(new SetInnerHTMLCommand($notice, ""));
	}

	public function getFailureCommand()
	{
		$element = new GetElementByIdCommand(getInputParameter("id"));
		$notice = new GetElementByIdCommand("register_notice");
		return (new SetAttributeCommand($element, [
			"validity" => "invalid"
		]))->withSubcommands(new SetInnerHTMLCommand($notice, ErrorMessage::getResultMessage($this->getObjectStatus())));
	}

	protected function prevalidate(&$arr)
	{
		$f = __METHOD__; //RegistrationUsernameValidator::getShortClass()."(".static::getShortClass().")->prevalidate()";
		try{
			$print = false;
			if(!array_key_exists("name", $arr)){
				Debug::warning("{$f} name array key does not exist");
				Debug::printArray($arr);
				Debug::printStackTrace();
			}elseif($print){
				Debug::print("{$f} received the following array:");
				Debug::printArray($arr);
			}
			$this->setParameters([
				NameDatum::normalize($arr['name'])
			]);
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
