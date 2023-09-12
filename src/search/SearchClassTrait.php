<?php
namespace JulianSeymour\PHPWebApplicationFramework\search;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SearchClassTrait
{

	protected $searchClass;

	public function hasSearchClass()
	{
		return isset($this->searchClass) && is_string($this->searchClass) && class_exists($this->searchClass);
	}

	public function getSearchClass()
	{
		$f = __METHOD__; //"SearchClassTrait(".static::getShortClass().")->getSearchClass()";
		if(!$this->hasSearchClass()) {
			Debug::error("{$f} search class is undefined");
		}
		return $this->searchClass;
	}

	public function setSearchClass($sc)
	{
		$f = __METHOD__; //"SearchClassTrait(".static::getShortClass().")->setSearchClass()";
		if($sc == null) {
			unset($this->searchClass);
			return null;
		}elseif(!is_string($sc)) {
			Debug::error("{$f} input parameter must be a string");
		}elseif(empty($sc)) {
			Debug::error("{$f} input parameter cannot be an empty string");
		}elseif(! class_exists($sc)) {
			Debug::error("{$f} class \"{$sc}\" does not exist");
		}
		return $this->searchClass = $sc;
	}
}
