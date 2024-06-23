<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait MultipleSearchClassesTrait{

	use ArrayPropertyTrait;

	public function hasSearchClasses():bool{
		return $this->hasArrayProperty("searchClasses");
	}

	public function pushSearchClass(...$class):int{
		return $this->pushArrayProperty("searchClasses", ...$class);
	}

	public function setSearchClasses($classes){
		return $this->setArrayProperty("searchClasses", $classes);
	}

	public function getSearchClasses(){
		$f = __METHOD__;
		if(!$this->hasSearchClasses()){
			Debug::error("{$f} classes are undefined");
		}
		return $this->getProperty("searchClasses");
	}

	public function getSearchClassCount():int{
		return $this->getArrayPropertyCount("searchClasses");
	}
}
