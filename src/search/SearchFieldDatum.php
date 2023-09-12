<?php
namespace JulianSeymour\PHPWebApplicationFramework\search;

use JulianSeymour\PHPWebApplicationFramework\common\StaticHumanReadableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use function JulianSeymour\PHPWebApplicationFramework\substitute;

class SearchFieldDatum extends BooleanDatum implements StaticHumanReadableNameInterface{

	protected $fieldName;

	protected $searchClass;

	public function setSearchClass($class){
		$f = __METHOD__;
		if(!is_string($class)) {
			Debug::error("{$f} class is not a string");
		}elseif(! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}
		return $this->searchClass = $class;
	}

	public function setFieldName($name){
		return $this->fieldName = $name;
	}

	public function hasFieldName(){
		return isset($this->fieldName);
	}

	public function getFieldName(){
		$f = __METHOD__;
		if(!$this->hasFieldName()) {
			Debug::error("{$f} field name is undefined");
		}
		return $this->fieldName;
	}

	public function hasSearchClass(){
		return isset($this->searchClass) && is_string($this->searchClass) && class_exists($this->searchClass);
	}

	public function getSearchClass(){
		$f = __METHOD__;
		if(!$this->hasSearchClass()) {
			Debug::error("{$f} search class is undefined");
		}
		return $this->searchClass;
	}

	public static function getHumanReadableNameStatic(?StaticHumanReadableNameInterface $that = null){
		$class = $that->getSearchClass();
		$prettys = $class::getPrettyClassNames();
		return substitute(_("Search %1%"), $prettys);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->fieldName);
		unset($this->searchClass);
	}
}
