<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

trait ColumnDefiningTrait
{

	protected $columnDefinition;

	public function setColumnDefinition(Datum $column)
	{
		$f = __METHOD__; //"ColumnDefiningTrait(".static::getShortClass().")->setColumnDefinition()";
		if(!$column instanceof Datum) {
			Debug::error("{$f} this function only accepts datums");
		}
		return $this->columnDefinition = $column;
	}

	public function hasColumnDefinition()
	{
		return isset($this->columnDefinition);
	}

	public function getColumnDefinition(): Datum
	{
		$f = __METHOD__; //"ColumnDefiningTrait(".static::getShortClass().")->getColumnDefinition()";
		if(!$this->hasColumnDefinition()) {
			Debug::error("{$f} column definition is undefined");
		}
		return $this->columnDefinition;
	}
}