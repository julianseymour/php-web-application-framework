<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\timeout;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ResetSessionTimeoutCommand extends Command implements JavaScriptInterface
{

	public static function getCommandId(): string
	{
		return "refreshSession";
	}

	public function toJavaScript(): string
	{
		return "resetSessionTimeoutAnimation()";
	}
}
