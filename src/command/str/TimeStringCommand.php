<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\getTimeStringFromTimestamp;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class TimeStringCommand extends StringTransformationCommand
{

	public static function getCommandId(): string
	{
		return "toLocaleTimeString";
	}

	public function evaluate(?array $params = null)
	{
		$ts = $this->getSubject();
		while ($ts instanceof ValueReturningCommandInterface) {
			$ts = $ts->evaluate();
		}
		return getTimeStringFromTimestamp($ts);
	}

	public function toJavaScript(): string
	{
		$ts = $this->getSubject();
		if ($ts instanceof JavaScriptInterface) {
			$ts = $ts->toJavaScript();
		}
		return "parseTimeStringFromTimestamp({$ts})";
	}
}
