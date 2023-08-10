<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class AlterColumnOption extends AlterOption
{

	use ColumnNameTrait;

	public function __construct($columnName)
	{
		parent::__construct();
		$this->setColumnName($columnName);
	}

	public function toSQL(): string
	{
		return "alter column " . $this->getColumnName() . " ";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->columnName);
	}
}