<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

/**
 * Does nothing
 *
 * @author j
 */
class NoOpCommand extends Command implements JavaScriptInterface
{

	public static function getCommandId(): string
	{
		return "noop";
	}

	public function toJavaScript(): string
	{
		return "";
	}
}
