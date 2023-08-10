<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class DateTimeStringCommand extends DateStringCommand
{

	public static function getCommandId(): string
	{
		return "DateTime";
	}

	public function evaluate(?array $params = null)
	{
		$ts = $this->getSubject();
		while ($ts instanceof ValueReturningCommandInterface) {
			$ts = $ts->evaluate();
		}
		$timezone = $this->getTimezone();
		while ($timezone instanceof ValueReturningCommandInterface) {
			$timezone = $timezone->evaluate();
		}
		if ($this->hasFormat()) {
			$format = $this->getFormat();
			while ($format instanceof ValueReturningCommandInterface) {
				$format = $format->evaluate();
			}
		} else {
			$format = null;
		}
		return getDateTimeStringFromTimestamp($ts, $timezone, $format);
	}

	public function toJavaScript(): string
	{
		$ts = $this->getSubject();
		if ($ts instanceof JavaScriptInterface) {
			$ts = $ts->toJavaScript();
		}
		return "parseTimestamp({$ts})";
	}
}
