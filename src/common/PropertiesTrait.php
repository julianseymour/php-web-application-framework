<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\Event;
use JulianSeymour\PHPWebApplicationFramework\event\SetManagedPropertyEvent;
use JulianSeymour\PHPWebApplicationFramework\validate\TypeValidator;

/**
 * a trait for classes that have a property called $properties which is just an associative array
 *
 * @author j
 */
trait PropertiesTrait{

	protected $properties;

	protected $propertyTypes;

	public function hasPropertyType($key): bool{
		if(isset($this->propertyTypes) && is_array($this->propertyTypes) && array_key_exists($key, $this->propertyTypes)){
			return true;
		}elseif($this instanceof StaticPropertyTypeInterface){
			$types = $this->declarePropertyTypes($this);
			$ret = array_key_exists($key, $types);
			deallocate($types);
			return $ret;
		}
		return false;
	}

	public function getProperties(...$keys){
		$f = __METHOD__;
		if(!$this->hasProperties(...$keys)){
			Debug::error("{$f} properties are undefined");
		}elseif(isset($keys) && count($keys) > 0){
			$ret = [];
			foreach($keys as $key){
				$ret[$key] = $this->getProperty($key);
			}
			return $ret;
		}
		return $this->properties;
	}

	public function setProperties(array $properties): array{
		foreach($properties as $key => $value){
			$this->setProperty($key, $value);
		}
		return $properties;
	}

	public function hasProperties(...$keys):bool{
		if(!isset($this->properties) || !is_array($this->properties) || empty($this->properties)){
			return false;
		}elseif(!isset($keys) || count($keys) === 0){
			return true;
		}
		foreach($keys as $key){
			if(!$this->hasProperty($key)){
				return false;
			}
		}
		return true;
	}
	
	public function hasPropertyTypes(...$keys):bool{
		if(!isset($this->propertyTypes) || !is_array($this->propertyTypes) || empty($this->propertyTypes)){
			return false;
		}elseif(!isset($keys) || count($keys) === 0){
			return true;
		}
		foreach($keys as $key){
			if(!$this->hasPropertyType($key)){
				return false;
			}
		}
		return true;
	}
	
	public function getPropertyTypes(...$keys){
		$f = __METHOD__;
		if(!$this->hasPropertyTypes(...$keys)){
			Debug::error("{$f} properties are undefined");
		}elseif(isset($keys) && count($keys) > 0){
			$ret = [];
			foreach($keys as $key){
				$ret[$key] = $this->getPropertyType($key);
			}
			return $ret;
		}
		return $this->propertyTypes;
	}
	
	public function validatePropertyType($key, $value): int{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasPropertyType($key)){
			if($print){
				Debug::warning("{$f} type check undefined for index \"{$key}\"");
			}
			return SUCCESS;
		}elseif(isset($this->propertyTypes) && is_array($this->propertyTypes) && array_key_exists($key, $this->propertyTypes)){
			$check = $this->propertyTypes[$key];
		}elseif($this instanceof StaticPropertyTypeInterface){
			$check = $this->getPropertyTypeStatic($key, $this);
			if($check === null){
				if($print){
					Debug::print("{$f} getPropertyTypeStatic returned null");
				}
				return SUCCESS;
			}
		}
		$status = TypeValidator::validateType($value, $check);
		deallocate($check);
		if($print){
			if($status === SUCCESS){
				Debug::print("{$f} validation successful");
			}else{
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} TypeValidator::validateType returned error status \"{$err}\"");
			}
		}
		return $status;
	}

	public function ejectProperty($key){
		if(!$this->hasProperty($key)){
			return null;
		}
		$value = $this->getProperty($key);
		if($value !== null && !$this->getDisableClaimFlag()){
			$this->release($value);
		}
		unset($this->properties[$key]);
		//$this->setProperty($key, null);
		return $value;
	}

	public function requirePropertyType($key, $type_str){
		if(!isset($this->propertyTypes) || !is_array($this->propertyTypes)){
			$this->propertyTypes = [
				$key => $type_str
			];
		}
		return $this->propertyTypes[$key] = $type_str;
	}

	public function hasProperty($key):bool{
		return $this->hasProperties() && array_key_exists($key, $this->properties);
	}
	
	public function setProperty($key, $value){
		$f = __METHOD__;
		$print = $this->getDebugFlag() && $key === "columns";
		if(!isset($this->properties) || !is_array($this->properties)){
			$this->properties = [];
		}elseif($this->hasProperty($key)){
			if(!$this->getDisableClaimFlag()){
				//Debug::print($this->properties[$key]);
				//Debug::error("{$f} temporarily disabled resetting property {$key} for this ".$this->getDebugString());
				$this->releaseProperty($key);
			}elseif($key === null){
				unset($this->properties[$key]);
			}
		}
		if(!$this->getDisableClaimFlag()){
			$this->claim($value);
		}
		if($this instanceof Basic && !$this instanceof Event && $this->hasAnyEventListener(EVENT_SET_PROPERTY)){
			$this->dispatchEvent(new SetManagedPropertyEvent($key, $value));
		}
		if($print){
			Debug::printStackTraceNoExit("{$f} about to assign property \"{$key}\" for this ".$this->getDebugString());
		}
		return $this->properties[$key] = $value;
	}

	public function getProperty($key){
		$f = __METHOD__;
		if(!$this->hasProperty($key)){
			$sc = get_short_class($this);
			$decl = $this->getDeclarationLine();
			$did = $this->getDebugId();
			Debug::error("{$f} property \"{$key}\" is undefined for this {$sc} declared {$decl} with debug ID {$did}");
		}
		return $this->properties[$key];
	}

	public function withProperty($key, $value): object{
		$this->setProperty($key, $value);
		return $this;
	}
	
	public function setPropertyTypes(?array $types):?array{
		if($types === null){
			unset($this->propertyTypes);
			return null;
		}
		return $this->propertyTypes = $types;
	}
	
	protected function copyProperties(ReplicableInterface $that):void{
		if($that->hasProperties()){
			foreach($that->getProperties() as $key => $value){
				$this->setProperty($key, replicate($value));
			}
		}
		if($that->hasPropertyTypes()){
			$this->setPropertyTypes(replicate($that->getPropertyTypes()));
		}
	}
	
	public function releaseProperties(bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasProperties()){
			Debug::error("{$f} no properties to release");
		}
		foreach(array_keys($this->properties) as $name){
			$this->releaseProperty($name, $deallocate);
		}
	}
	
	public function releaseProperty(string $name, bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
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
