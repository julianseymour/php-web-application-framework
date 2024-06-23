<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * Trait for objets that have a dynamically assignable Element class.
 * If one is not assgined, getElementClass will look for a function called getElementClassStatic before
 * throwing an error
 *
 * @author j
 */
trait ElementBindableTrait{

	protected $elementClass;

	public function hasElementClass():bool{
		return isset($this->elementClass);
	}

	public function setElementClass($class){
		$f = __METHOD__;
		if(!is_string($class)){
			Debug::error("{$f} class is not a string");
		}elseif(!class_exists($class)){
			Debug::error("{$f} class \"{$class}\" does not exist");
		}elseif($this->hasElementClass()){
			$this->release($this->elementClass);
		}
		return $this->elementClass = $this->claim($class);
	}

	public function getElementClass(){
		$f = __METHOD__;
		if(!isset($this->elementClass)){
			if($this instanceof StaticElementClassInterface){
				return $this->getElementClassStatic($this);
			}
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} element class is undefined. Declared {$decl}");
		}
		return $this->elementClass;
	}
}
