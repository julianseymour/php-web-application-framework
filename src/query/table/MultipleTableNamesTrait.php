<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultipleTableNamesTrait
{

	use ArrayPropertyTrait;

	public function setTableNames($values)
	{
		return $this->setArrayProperty("tableNames", $values);
	}

	public function hasTableNames()
	{
		return $this->hasArrayProperty("tableNames");
	}

	public function getTableNames()
	{
		return $this->getProperty("tableNames");
	}

	public function pushTableNames(...$values)
	{
		return $this->pushArrayProperty("tableNames", ...$values);
	}

	public function mergeTableNames($values)
	{
		return $this->mergeArrayProperty("tableNames", $values);
	}

	public function getTableNameCount()
	{
		return $this->getArrayPropertyCount("tableNames");
	}
}