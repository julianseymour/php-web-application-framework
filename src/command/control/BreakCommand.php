<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class BreakCommand extends Command implements JavaScriptInterface{

	public static function getCommandId(): string{
		return "break";
	}

	public function toJavaScript(): string{
		return static::getCommandId();
	}
}
