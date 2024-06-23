<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\getDateStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\PhoneNumberInput;
use DateTime;
use DateTimeZone;
use Exception;

class TimestampDatum extends SignedIntegerDatum implements StaticElementClassInterface{

	public function __construct($name){
		parent::__construct($name, 64);
		// $this->setElementClass(DateTimeLocalInput::class);
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return PhoneNumberInput::class;
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"updateToCurrentTime"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"updateToCurrentTime"
		]);
	}
	
	public function setUpdateToCurrentTimeFlag(bool $value = true):bool{
		return $this->setFlag("updateToCurrentTime", $value);
	}

	public function getUpdateToCurrentTimeFlag():bool{
		return $this->getFlag("updateToCurrentTime");
	}

	public function getProcessValuelessInputFlag():bool{
		return parent::getProcessValuelessInputFlag() || $this->getUpdateToCurrentTimeFlag();
	}

	public function cast($v){
		$f = __METHOD__;
		try{
			$print = false;
			if(is_string($v)){
				if($print){
					Debug::print("{$f} casting the string \"{$v}\"");
				}
				if(preg_match('/^0|^\-?([1-9]+[0-9]*)$/', $v)){
					if($print){
						Debug::print("{$f} the regular expression is satisfied; value is probably a unix timestamp");
					}
					$v = intval($v);
				}else{
					if($print){
						Debug::print("{$f} regular expression is not satisfied");
					}
					$datetimezone = new DateTimeZone(user()->getTimezone());
					try{
						$datetime = new DateTime($v, $datetimezone);
						$parsed = $datetime->getTimestamp();
						if($print){
							Debug::error("{$f} parsed value \"{$parsed}\" from DateTimeZone of value \"{$v}\"; this is the wrong venue for content negotiation");
						}
					}catch(Exception $y){
						Debug::error("{$f} something went wrong constructing a DateTime: \"{$y}\"");
					}
				}
			}elseif($print){
				Debug::print("{$f} value is not a string");
			}
			if($print){
				Debug::print("{$f} cast to \"{$v}\"");
			}
			return $v;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function processInput($input){
		$f = __METHOD__;
		try{
			if(!$this->getUpdateToCurrentTimeFlag()){
				return parent::processInput($input);
			}
			$this->setValue(time());
			$this->setUpdateFlag(true);
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getHumanWritableValue(){
		return getDateStringFromTimestamp($this->getValue(), new DateTimeZone(user()->getTimezone()));
	}

	public function getHumanReadableName(){
		if(!isset($this->humanReadableName)){
			return _("Timestamp");
		}
		return parent::getHumanReadableName();
	}

	public function hasHumanReadableName(){
		return true;
	}
}
