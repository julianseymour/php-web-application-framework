<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\Event;
use JulianSeymour\PHPWebApplicationFramework\event\ReleasePropertyKeyEvent;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;

/**
 * Provides shortcuts for array property push, merge, etc with optional type checking
 *
 * @author j
 */
trait ArrayPropertyTrait{

	use PropertiesTrait;
	
	public function setArrayProperty($key, $values): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			if($values === null || !isset($values) || count($values) < 1 || empty($values)){
				if(!isset($this->properties) || !is_array($this->properties) || !array_key_exists($key, $this->properties)){
					return null;
				}
				if($print){
					Debug::print("{$f} prior to unset, here are all array properties");
					Debug::printArray(array_keys($this->properties));
					Debug::print("{$f} prior to unset, this is array property \"{$key}\"");
					Debug::printArray(array_keys($this->properties[$key]));
				}
				unset($this->properties[$key]);
				// FYI: unset(array[$index]) removes the index completely
				if(empty($this->properties)){
					unset($this->properties);
				}
				return null;
			}elseif(!is_array($values)){
				Debug::error("{$f} this function accepts only null and arrays");
			} else{
				foreach($values as $value){
					if(!$this->validatePropertyType($key, $value)){
						$gottype = is_object($value) ? $value->getClass() : gettype($value);
						Debug::error("{$f} value of type \"{$gottype}\" failed type check for array {$key}");
					}
				}
			}
			$repacked = [];
			$count = 0;
			foreach($values as $offset => $value){
				if($value instanceof ArrayKeyProviderInterface){
					if($print){
						Debug::print("{$f} first item is an instanceof ArrayKeyProviderInterface");
					}
					$offset = $value->getArrayKey($count);
				}
				if($print){
					Debug::print("{$f} repacking array value into offset \"{$offset}\"");
				}
				$repacked[$offset] = $value;
				$count++;
			}
			if($print){
				Debug::print("{$f} about to print repacked keys");
				Debug::printArray(array_keys($repacked));
			}
			$ret = $this->setProperty($key, $repacked);
			if(!$this->hasArrayProperty($key)){
				Debug::error("{$f} immediately after setting array property \"{$key}\", it is undefined");
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	private function insertArrayProperty(string $where, $key, ...$values): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				Debug::print("{$f} entered for ".$this->getDebugString());
			}
			if(!isset($values)){
				Debug::error("{$f} received null parameter");
			}
			foreach($values as $value){
				if(!$this->validatePropertyType($key, $value)){
					$value = ! is_object($value) || $value instanceof StringifiableInterface ? $value : "Non-stirngifiable object";
					Debug::error("{$f} value \"{$value}\" failed type check");
				}
				if(!$this->getDisableClaimFlag()){
					$this->claim($value);
				}
			}
			if(!isset($this->properties) || !is_array($this->properties)){
				if($print){
					Debug::print("{$f} properties were completely undefined");
				}
				$this->properties = [
					$key => []
				];
			}elseif(!array_key_exists($key, $this->properties) || !is_array($this->properties[$key])){
				if($print){
					Debug::print("{$f} array property \"{$key}\" was not previously defined");
				}
				$this->properties[$key] = [];
			}
			$int = false;
			$assoc = false;
			$pushed = 0;
			foreach($values as $offset => $value){
				$gottype = gettype($value);
				$count = $this->getArrayPropertyCount($key);
				if(is_object($value) && $value instanceof ArrayKeyProviderInterface){
					if($int){
						Debug::error("{$f} please do not mix integer and string array keys");
					}elseif($print){
						Debug::print("{$f} {$gottype} is an instanceof ArrayKeyProviderInterface");
					}
					$assoc = true;
					$offset = $value->getArrayKey($count);
				}elseif(is_string($offset)){
					if($int){
						Debug::error("{$f} please do not mix integer and string array keys");
					}
					$assoc = true;
				}elseif($assoc){
					Debug::error("{$f} please do not mix integer and string array keys");
				}else{
					if($print){
						Debug::print("{$f} {$gottype} is not an instanceof ArrayKeyProviderInterface");
					}
					$int = true;
					continue;
				}
				$this->properties[$key][$offset] = $value;
				if(count($this->properties[$key]) > $count){
					$pushed++;
				}
			}
			if($assoc){
				return $pushed;
			}
			switch($where){
				case CONST_BEFORE:
					return array_unshift($this->properties[$key], ...$values);
				case CONST_AFTER:
					return array_push($this->properties[$key], ...$values);
				default:
					Debug::error("{$f} invalid parameter \"{$where}\"");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function unshiftArrayProperty($key, ...$values): int{
		return $this->insertArrayProperty(CONST_BEFORE, $key, ...$values);
	}

	public function pushArrayProperty($key, ...$values): int{
		return $this->insertArrayProperty(CONST_AFTER, $key, ...$values);
	}

	public function hasArrayProperty($key):bool{
		return isset($this->properties) && is_array($this->properties) && array_key_exists($key, $this->properties) && is_array($this->properties[$key]) && !empty($this->properties[$key]);
	}

	public function mergeArrayProperty($key, array $values): array{
		$f = __METHOD__;
		if(!isset($values)){
			Debug::error("{$f} received null parameter");
		}elseif(!is_array($values)){
			Debug::error("{$f} values must be an array");
		}elseif(empty($values)){
			return $this->hasArrayProperty($key) ? $this->getProperty($key) : [];
		}
		$repacked = [];
		foreach($values as $offset => $value){
			if($value === null){
				Debug::error("{$f} one of the things passed to this function is null");
			}elseif(!$this->validatePropertyType($key, $value)){
				Debug::error("{$f} value failed type check");
			}elseif($value instanceof ArrayKeyProviderInterface){
				$repacked[$value->getArrayKey(count($repacked))] = $value;
			}elseif(is_string($offset)){
				$repacked[$offset] = $value;
			}else{
				array_push($repacked, $value);
			}
		}
		if(!$this->hasArrayProperty($key)){
			return $this->setArrayProperty($key, $repacked);
		}
		if(!$this->getDisableClaimFlag()){
			foreach($this->getProperty($key) as $value){
				$this->release($value);
			}
		}
		return $this->setArrayProperty($key, array_merge($this->getProperty($key), $repacked));
	}

	public function getArrayPropertyKeys($key): array{
		if(!$this->hasArrayProperty($key)){
			return [];
		}
		return array_keys($this->getProperty($key));
	}

	public function getArrayPropertyCount(string $key): int{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasArrayProperty($key)){
			if($print){
				Debug::print("{$f} no array property \"{$key}\"");
			}
			return 0;
		}
		$count = count($this->getProperty($key));
		if($print){
			Debug::print("{$f} count for array property \"{$key}\" is {$count}");
		}
		return $count;
	}

	public function getArrayPropertyValueAtOffset($key1, int $offset){
		$f = __METHOD__;
		if($offset > $this->getArrayPropertyCount($key1) - 1){
			Debug::error("{$f} offset {$offset} exceeds count of array property \"{$key1}\"");
		}
		$key2 = $this->getArrayPropertyKeys($key1)[$offset];
		return $this->getArrayPropertyValue($key1, $key2);
	}

	public function hasArrayPropertyKey($key1, $key2): bool{
		$f = __METHOD__;
		$print = false;
		if(is_array($key2)){
			Debug::warning("{$f} second key is an array");
			Debug::printArray($key2);
			Debug::printStackTrace();
		}
		if(!is_int($key1) && ! is_string($key1)){
			Debug::error("{$f} first key is neither integer nor string");
		}elseif(!is_int($key2) && ! is_string($key2)){
			if($key2 instanceof StringifiableInterface){
				Debug::error("{$f} second key is \"{$key2}\"");
			}
			$gottype = is_object($key2) ? $key2->getClass() : gettype($key2);
			Debug::error("{$f} second key is a {$gottype}");
		}elseif($print){
			if($this->hasArrayProperty($key1)){
				Debug::print("{$f} yes, this object has a property \"{$key1}\"");
				if(array_key_exists($key2, $this->getProperty($key1))){
					Debug::print("{$f} yes, array \"{$key1}\" has a member \"{$key2}\"");
				}else{
					Debug::print("{$f} no, array \"{$key1}\" does not have a member \"{$key2}\"");
				}
			}else{
				Debug::print("{$f} no, this object does not have a property \'{$key1}\"");
			}
		}
		return $this->hasArrayProperty($key1) && array_key_exists($key2, $this->getProperty($key1));
	}

	public function getArrayPropertyValue($key1, $key2){
		$f = __METHOD__;
		if(!$this->hasArrayPropertyKey($key1, $key2)){
			Debug::error("{$f} this object does not have an array property \"{$key1}\" with a value at \"{$key2}\"");
		}
		return $this->getProperty($key1)[$key2];
	}

	public function setArrayPropertyValue($key1, $key2, $value){
		$f = __METHOD__;
		$print = $value instanceof Basic && $value->getDebugFlag();
		$ret = $this->setArrayPropertyValues($key1, [
			$key2 => $value
		]);
		if($print){
			if($value === null){
				if($this->getArrayPropertyValue($key1, $key2) !== null){
					Debug::error("{$f} setArrayPropertyValues is busted");
				}
			}
		}
		if($ret == null){
			Debug::warning("{$f} setArrayPropertyValues returned null");
			return $ret;
		}
		return $value;
	}

	public function unsetArrayPropertyValue($key1, $key2){
		if(!$this->getDisableClaimFlag()){
			$this->releaseArrayPropertyKey($key1, $key2);
		}
		unset($this->properties[$key1][$key2]);
		return null;
	}

	public function setArrayPropertyValues($key1, $key2values): ?array{
		$f = __METHOD__;
		$print = false;
		if(empty($key2values)){
			Debug::error("{$f} key/value array is empty");
		}
		foreach($key2values as $key2 => $value){
			$status = $this->validatePropertyType($key1, $value);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} validatePropertyType returned error status \"{$err}\" for properry \"{$key1}\", key \"{$key2}\", value \"{$value}\"");
				return null;
			}
			if(!$this->hasArrayProperty($key1)){
				$this->setArrayProperty($key1, [
					$key2 => $value
				]);
			}else{
				if(!$this->getDisableClaimFlag()){
					if($this->hasArrayPropertyKey($key1, $key2)){
						$this->releaseArrayPropertyKey($key1, $key2);
					}
					$this->claim($value);
				}
				$this->properties[$key1][$key2] = $value;
			}
			if($print){
				if($value === null){
					if($this->getArrayPropertyValue($key1, $key2) !== null){
						Debug::error("{$f} nope");
					}else{
						Debug::print("{$f} yes, it worked");
					}
				}
			}
		}
		return $key2values;
	}
	
	public function releaseArrayPropertyKey($name, $key, bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasProperty($name)){
			Debug::error("{$f} property {$name} does not exist at all for this ".$this->getDebugString());
		}elseif(!$this->hasArrayPropertyKey($name, $key)){
			Debug::warning("{$f} {$name} array property member {$key} does not exist for this ".$this->getDebugString().". The following is a list of valid indices:");
			Debug::printArray($this->properties[$name]);
			Debug::printStackTrace();
		}
		$v = $this->getArrayPropertyValue($name, $key); //$this->properties[$name][$key];
		if($print){
			$ds = $this->getDebugString();
			Debug::print("{$f} releasing property {$name} key {$key} for {$ds}");
		}
		unset($this->properties[$name][$key]);
		if(empty($this->properties[$name])){
			unset($this->properties[$name]);
			if(empty($this->properties)){
				unset($this->properties);
			}
		}
		if($this instanceof Basic && !$this instanceof Event && $this->hasAnyEventListener(EVENT_RELEASE_PROPERTY_KEY)){
			$this->dispatchEvent(new ReleasePropertyKeyEvent($name, $key, $v));
		}
		if($this->getDisableClaimFlag()){
			if($print){
				Debug::print("{$f} unset property \"{$name}\" with key \"{$key}\" of ".$this->getDebugString());
			}
			return;
		}elseif($print){
			Debug::print("{$f} releasing property {$name} with key \"{$key}\" from ".$this->getDebugString());
		}
		$this->release($v, $deallocate);
		if($print){
			Debug::print("{$f} done releasing property {$name} key {$key} from this ".$this->getDebugString());
		}
	}
	
	public function releaseProperty(string $name, bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasArrayProperty($name)){
			$keys = array_keys($this->properties[$name]);
			if($print){
				Debug::print("{$f} property \"{$name}\" is an array property, with the following keys:");
				Debug::printArray($this->properties[$name]);
			}
			foreach($keys as $key){
				$this->releaseArrayPropertyKey($name, $key, $deallocate);
			}
			if($print){
				Debug::print("{$f} done releasing array property \"{$name}\"");
			}
		}else{
			if($print){
				Debug::print("{$f} releasing non-array property \"{$name}\"");
			}
			$property = $this->properties[$name];
			unset($this->properties[$name]);
			if(empty($this->properties)){
				unset($this->properties);
			}
			if($this->getDisableClaimFlag()){
				if($print){
					Debug::print("{$f} unset property \"{$name}\" of ".$this->getDebugString());
				}
				return;
			}elseif($print){
				Debug::print("{$f} releasing property {$name} from ".$this->getDebugString());
			}
			$this->release($property, $deallocate);
		}
	}
}
