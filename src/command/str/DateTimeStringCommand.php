<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use DateTimeZone;

class DateTimeStringCommand extends AbstractDateTimeStringCommand{

	public static function getCommandId(): string{
		return "DateTime";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$ts = $this->getSubject();
		while($ts instanceof ValueReturningCommandInterface){
			$ts = $ts->evaluate();
		}
		if($ts === null){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} timestamp is null. INstantiated {$decl}");
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
		return getDateTimeStringFromTimestamp($ts, $timezone, $format);
	}

	public function toJavaScript(): string{
		$ts = $this->getSubject();
		if($ts instanceof JavaScriptInterface){
			$ts = $ts->toJavaScript();
		}
		return "parseTimestamp({$ts})";
	}
}
