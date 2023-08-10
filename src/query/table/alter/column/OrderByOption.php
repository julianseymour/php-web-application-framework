<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class OrderByOption extends AlterOption
{

	use MultipleColumnNamesTrait;

	public function __construct(...$columnNames)
	{
		parent::__construct();
		$this->setColumnNames($columnNames);
	}

	public function toSQL(): string
	{
		return "order by " . $this->getColumnNameString();
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
	}
}
