<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

class DropColumnOption extends AlterColumnOption
{

	public function toSQL(): string
	{
		return "drop column " . $this->getColumnName();
	}
}
