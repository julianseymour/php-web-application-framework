<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\getTimeStringFromTimestamp;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use DateTimeZone;

class TimeStringCommand extends AbstractDateTimeStringCommand{

	public static function getCommandId(): string{
		return "toLocaleTimeString";
	}

	public function evaluate(?array $params = null){
		$ts = $this->getSubject();
		while($ts instanceof ValueReturningCommandInterface){
			$ts = $ts->evaluate();
		}
		$timezone = $this->getTimezone();
		while($timezone instanceof ValueReturningCommandInterface){
			$timezone = $timezone->evaluate();
		}
		if(!in_array($timezone, DateTimeZone::listIdentifiers())){
			$timezone = date_default_timezone_get();
		}
		if($this->hasFormat()){
			$format = $this->getFormat();
			while($format instanceof ValueReturningCommandInterface){
				$format = $format->evaluate();
			}
		}else{
			$format = null;
		}
		return getTimeStringFromTimestamp($ts, $timezone, $format);
	}

	public function toJavaScript(): string{
		$ts = $this->getSubject();
		if($ts instanceof JavaScriptInterface){
			$ts = $ts->toJavaScript();
		}
		return "parseTimeStringFromTimestamp({$ts})";
	}
}
