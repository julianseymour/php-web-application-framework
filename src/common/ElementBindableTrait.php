<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * Trait for objets that have a dynamically assignable Element class.
 * If one is not assgined, getElementClass will look for a function called getElementClassStatic before
 * throwing an error
 *
 * @author j
 */
trait ElementBindableTrait
{

	protected $elementClass;

	public function hasElementClass(){
		return isset($this->elementClass) && class_exists($this->elementClass);
	}

	public function setElementClass($class){
		$f = __METHOD__; //"ElementBindableTrait(".static::getShortClass().")->setElementClass()";
		if (! is_string($class)) {
			Debug::error("{$f} class is not a string");
		} elseif (! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}
		return $this->elementClass = $class;
	}

	public function getElementClass(){
		$f = __METHOD__; //"ElementBindableTrait(".static::getShortClass().")->getElementClass()";
		if (! isset($this->elementClass)) { // $this->hasElementClass()){
			if ($this instanceof StaticElementClassInterface) {
				return $this->getElementClassStatic($this);
			}
			Debug::error("{$f} element class is undefined");
		}
		return $this->elementClass;
	}
}
