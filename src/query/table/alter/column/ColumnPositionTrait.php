<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ColumnPositionTrait
{

	protected $columnPosition;

	protected $afterColumnName;

	public function setColumnPosition($position, $afterColumnName = null)
	{
		$f = __METHOD__; //"ColumnPositionTrait(".static::getShortClass().")->setColumnPosition()";
		if($position === null) {
			return $this->columnPosition = null;
		}
		if(!is_string($position)) {
			Debug::error("{$f} position must be a string");
		}
		$position = strtolower($position);
		switch ($position) {
			case COLUMN_POSITION_AFTER:
			case COLUMN_POSITION_FIRST:
				break;
			default:
				Debug::error("{$f} invalid column position \"{$position}\"");
		}
		$this->columnPosition = $position;
		if($position === COLUMN_POSITION_AFTER && $afterColumnName !== null) {
			$this->setAfterColumnName($afterColumnName);
		}
		return $position;
	}

	public function getColumnPositionString()
	{
		$f = __METHOD__; //"ColumnPositionTrait(".static::getShortClass().")->getColumnPositionString()";
		if(!$this->hasColumnPositon()) {
			Debug::error("{$f} position is undefined");
		}
		$string = $this->getColumnPosition();
		if($string === COLUMN_POSITION_AFTER) {
			$string .= $this->getAfterColumnName();
		}
		return $string;
	}

	public function hasColumnPositon()
	{
		return isset($this->columnPosition) && ! empty($this->columnPosition);
	}

	public function getColumnPosition()
	{
		$f = __METHOD__; //"ColumnPositionTrait(".static::getShortClass().")->getColumnPosition()";
		if(!$this->hasColumnPositon()) {
			Debug::error("{$f} position is undefined");
		}
		return $this->columnPosition;
	}

	public function setAfterColumnName($afterColumnName)
	{
		return $this->afterColumnName = $afterColumnName;
	}

	public function hasAfterColumnName()
	{
		return isset($this->afterColumnName) && ! empty($this->afterColumnName);
	}

	public function getAfterColumnName()
	{
		$f = __METHOD__; //"ColumnPositionTrait(".static::getShortClass().")->getAfterColumnName()";
		if(!$this->hasAfterColumnName()) {
			Debug::error("{$f} after column name is undefined");
		}
		return $this->afterColumnName;
	}
}
