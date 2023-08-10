<?php
namespace JulianSeymour\PHPWebApplicationFramework\cache;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class CachePageContentCommand extends Command implements JavaScriptInterface
{

	public function toJavaScript(): string
	{
		return "cachePageContent();";
	}

	public static function getCommandId(): string
	{
		return "cachePageContent";
	}
}
