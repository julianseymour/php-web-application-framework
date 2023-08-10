<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class NullCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "null";
	}

	public function evaluate(?array $params = null)
	{
		return null;
	}

	public function toJavaScript(): string
	{
		return "null";
	}
}
