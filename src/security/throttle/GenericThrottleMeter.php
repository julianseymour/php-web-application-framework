<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\throttle;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class GenericThrottleMeter extends ThrottleMeterData
{

	protected $throttledObject;

	public function setThrottledObject($object)
	{
		return $this->throttledObject = $object;
	}

	public function hasThrottledObject()
	{
		return isset($this->throttledObject);
	}

	public function getThrottledObject()
	{
		$f = __METHOD__; //GenericThrottleMeter::getShortClass()."(".static::getShortClass().")->getThrottledObject()";
		try{
			if(!$this->hasThrottledObject()){
				Debug::error("{$f} throttled object is undefined");
				return null;
			}
			return $this->throttledObject;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getThrottleSelectOperands()
	{
		return [
			"insertIpAddress"
		];
	}

	public static function getThrottleSelectOperators()
	{
		return [
			OPERATOR_EQUALS
		];
	}
}
