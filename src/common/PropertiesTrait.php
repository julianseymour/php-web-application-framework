<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\array_remove_key;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
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
		if(isset($this->propertyTypes) && is_array($this->propertyTypes) && array_key_exists($key, $this->propertyTypes)) {
			return true;
		}elseif($this instanceof StaticPropertyTypeInterface) {
			$types = $this->declarePropertyTypes($this);
			return array_key_exists($key, $types);
		}
		return false;
	}

	public function getProperties(){
		return $this->properties;
	}

	public function setProperties(array $properties): array{
		foreach($properties as $key => $value) {
			$this->setProperty($key, $value);
		}
		return $properties;
	}

	public function validatePropertyType($key, $value): int{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasPropertyType($key)) {
			if($print) {
				Debug::warning("{$f} type check undefined for index \"{$key}\"");
			}
			return SUCCESS;
		}elseif(isset($this->propertyTypes) && is_array($this->propertyTypes) && array_key_exists($key, $this->propertyTypes)) {
			$check = $this->propertyTypes[$key];
		}elseif($this instanceof StaticPropertyTypeInterface) {
			$check = $this->getPropertyTypeStatic($key, $this);
			if($check === null) {
				if($print) {
					Debug::print("{$f} getPropertyTypeStatic returned null");
				}
				return SUCCESS;
			}
		}
		$status = TypeValidator::validateType($value, $check);
		if($print) {
			if($status === SUCCESS) {
				Debug::print("{$f} validation successful");
			}else{
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} TypeValidator::validateType returned error status \"{$err}\"");
			}
		}
		return $status;
	}

	public function ejectProperty($key){
		if(!$this->hasProperty($key)) {
			return null;
		}
		$ret = $this->getProperty($key);
		$this->setProperty($key, null);
		return $ret;
	}

	public function requirePropertyType($key, $type_str){
		if(! isset($this->propertyTypes) || ! is_array($this->propertyTypes)) {
			$this->propertyTypes = [
				$key => $type_str
			];
		}
		return $this->propertyTypes[$key] = $type_str;
	}

	public function hasProperty($key){
		return isset($this->properties) && is_array($this->properties) && array_key_exists($key, $this->properties);
	}

	public function setProperty($key, $value){
		if(! isset($this->properties) || ! is_array($this->properties)) {
			$this->properties = [];
		}
		if($key === null && array_key_exists($key, $this->properties)) {
			$this->properties = array_remove_key($this->properties, $key);
		}
		return $this->properties[$key] = $value;
	}

	public function getProperty($key){
		$f = "PropertiesTrait(".static::getShortClass().")->getProperty()";
		if(!$this->hasProperty($key)) {
			Debug::error("{$f} property \"{$key}\" is undefined");
		}
		return $this->properties[$key];
	}

	public function withProperty($key, $value): object
	{
		$this->setProperty($key, $value);
		return $this;
	}
}
