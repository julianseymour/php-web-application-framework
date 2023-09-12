<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DataStructureClassTrait
{

	public function setDataStructureClass($class)
	{
		$f = __METHOD__; //"DataStructureClassTrait(".static::getShortClass().")->setDataStructureClass()";
		$print = false;
		if(!is_string($class)) {
			Debug::error("{$f} class is not a string");
		}elseif(empty($class)) {
			Debug::error("{$f} class name is empty string");
		}elseif(! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}elseif($print) {
			Debug::print("{$f} setting data structure class to \"{$class}\"");
		}
		return $this->dataStructureClass = $class;
	}

	public function hasDataStructureClass()
	{
		return isset($this->dataStructureClass) && class_exists($this->dataStructureClass) && is_a($this->dataStructureClass, DataStructure::class, true);
	}

	public function getDataStructureClass()
	{
		$f = __METHOD__; //"DataStructureClassTrait(".static::getShortClass().")->getDataStructureClass()";
		if(!$this->hasDataStructureClass()) {
			Debug::error("{$f} data structure class is undefined");
		}
		return $this->dataStructureClass;
	}
}