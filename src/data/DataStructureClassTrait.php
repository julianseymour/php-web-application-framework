<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DataStructureClassTrait{

	protected $dataStructureClass;
	
	public function setDataStructureClass($class){
		$f = __METHOD__;
		$print = false;
		if(!is_string($class)){
			Debug::error("{$f} class is not a string");
		}elseif(empty($class)){
			Debug::error("{$f} class name is empty string");
		}elseif(!class_exists($class)){
			Debug::error("{$f} class \"{$class}\" does not exist");
		}elseif($print){
			Debug::print("{$f} setting data structure class to \"{$class}\"");
		}elseif($this->hasDataStructureClass()){
			$this->release($this->dataStructureClass);
		}
		return $this->dataStructureClass = $this->claim($class);
	}

	public function hasDataStructureClass():bool{
		return isset($this->dataStructureClass);
	}

	public function getDataStructureClass(){
		$f = __METHOD__;
		if(!$this->hasDataStructureClass()){
			Debug::error("{$f} data structure class is undefined");
		}
		return $this->dataStructureClass;
	}
}