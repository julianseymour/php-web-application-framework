<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class ModifyColumnOption extends AlterOption
{

	use ColumnPositionTrait;
	use ColumnDefiningTrait;

	public function __construct($columnDefinition, $position = null, $afterColumnName = null)
	{
		parent::__construct();
		$this->setColumnDefinition($columnDefinition);
		if($position !== null) {
			$this->setColumnPosition($position, $afterColumnName);
		}
	}

	public function toSQL(): string
	{
		$definition = $this->getColumnDefinition();
		$string = "modify {$definition}";
		if($this->hasColumnPositon()) {
			$string .= $this->getColumnPositionString();
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->columnDefinition);
		unset($this->columnPosition);
		unset($this->afterColumnName);
	}
}
