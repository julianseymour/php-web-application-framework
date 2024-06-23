<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultipleTableNamesTrait{

	use ArrayPropertyTrait;

	public function setTableNames($values):?array{
		return $this->setArrayProperty("tableNames", $values);
	}

	public function hasTableNames():bool{
		return $this->hasArrayProperty("tableNames");
	}

	public function getTableNames(){
		return $this->getProperty("tableNames");
	}

	public function pushTableNames(...$values):int{
		return $this->pushArrayProperty("tableNames", ...$values);
	}

	public function mergeTableNames($values):array{
		return $this->mergeArrayProperty("tableNames", $values);
	}

	public function getTableNameCount():int{
		return $this->getArrayPropertyCount("tableNames");
	}
}