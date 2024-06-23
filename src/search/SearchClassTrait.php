<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SearchClassTrait{

	protected $searchClass;

	public function hasSearchClass():bool{
		return isset($this->searchClass) && is_string($this->searchClass) && class_exists($this->searchClass);
	}

	public function getSearchClass(){
		$f = __METHOD__;
		if(!$this->hasSearchClass()){
			Debug::error("{$f} search class is undefined for this ".$this->getDebugString());
		}
		return $this->searchClass;
	}
	
	public function setSearchClass($class){
		$f = __METHOD__;
		if(!is_string($class)){
			Debug::error("{$f} class is not a string");
		}elseif(empty($class)){
			Debug::error("{$f} input parameter cannot be an empty string");
		}elseif(!class_exists($class)){
			Debug::error("{$f} class \"{$class}\" does not exist");
		}elseif($this->hasSearchClass()){
			$this->release($this->searchClass);
		}
		return $this->searchClass = $this->claim($class);
	}
}
