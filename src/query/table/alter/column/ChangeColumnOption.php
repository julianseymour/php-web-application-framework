<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

/**
 * For this class, $columnName refers to the old column name.
 * The new one can be retrieved with $columnDefinition->getColumnName()
 *
 * @author j
 */
class ChangeColumnOption extends ModifyColumnOption
{

	use ColumnNameTrait;

	public function __construct($oldColumnName, $newColumnDefinition, $position = null, $afterColumnName = null)
	{
		parent::__construct($newColumnDefinition, $position, $afterColumnName);
		$this->setColumnName($oldColumnName);
	}

	public function toSQL(): string
	{
		$oldName = $this->getColumnName();
		$newDefinition = $this->getColumnDefinition()->toSQL();
		$string = "change {$oldName} {$newDefinition}";
		if ($this->hasColumnPositon()) {
			$string .= $this->getColumnPositionString();
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->columnName);
	}
}
