<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class DocumentVisibilityStateCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "visibilityState";
	}

	public function toJavaScript(): string
	{
		return "document.visibilityState";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //DocumentVisibilityStateCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		ErrorMessage::unimplemented($f);
	}
}
