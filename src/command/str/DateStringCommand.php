<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\getDateStringFromTimestamp;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use DateTimeZone;

class DateStringCommand extends StringTransformationCommand{

	protected $timezone;

	protected $format;

	public function __construct($subject, $timezone = null, ?string $format = null){
		parent::__construct($subject);
		if($timezone !== null) {
			$this->setTimezone($timezone);
		}
		if($format !== null) {
			$this->setFormat($format);
		}
	}

	public static function getCommandId(): string{
		return "toDateString";
	}

	public function hasTimezone(): bool{
		return isset($this->timezone);
	}

	public function setTimezone($timezone){
		if($timezone === null) {
			unset($this->timezone);
			return null;
		}
		return $this->timezone = $timezone;
	}

	public function getTimezone(){
		if($this->hasTimezone()) {
			return $this->timezone;
		}
		return null;
	}

	public function hasFormat(): bool{
		return isset($this->format);
	}

	public function setFormat($format){
		if($format === null) {
			unset($this->format);
			return null;
		}
		return $this->format = $format;
	}

	public function getFormat(){
		$f = __METHOD__;
		if(!$this->hasFormat()) {
			Debug::error("{$f} format is undefined");
		}
		return $this->format;
	}

	public function evaluate(?array $params = null){
		$ts = $this->getSubject();
		while ($ts instanceof ValueReturningCommandInterface) {
			$ts = $ts->evaluate();
		}
		$timezone = $this->getTimezone();
		while ($timezone instanceof ValueReturningCommandInterface) {
			$timezone = $timezone->evaluate();
		}
		if(!in_array($timezone, DateTimeZone::listIdentifiers())){
			$timezone = date_default_timezone_get();
		}
		if($this->hasFormat()) {
			$format = $this->getFormat();
			while ($format instanceof ValueReturningCommandInterface) {
				$format = $format->evaluate();
			}
		}else{
			$format = null;
		}
		return getDateStringFromTimestamp($ts, $timezone, $format);
	}

	public function toJavaScript(): string{
		$ts = $this->getSubject();
		if($ts instanceof JavaScriptInterface) {
			$ts = $ts->toJavaScript();
		}
		return "parseDateStringFromTimestamp({$ts})";
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->format);
		unset($this->timezone);
	}
}
