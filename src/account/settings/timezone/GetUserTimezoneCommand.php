<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\settings\timezone;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetUserTimezoneCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	public function toJavaScript(): string
	{
		return "Intl.DateTimeFormat().resolvedOptions().timeZone";
	}

	public static function getCommandId(): string
	{
		return "getUserTimezone";
	}

	public function evaluate(?array $params = null)
	{
		return user()->getTimezone();
	}
}
