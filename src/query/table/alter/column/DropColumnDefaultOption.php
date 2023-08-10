<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

class DropColumnDefaultOption extends AlterColumnOption
{

	public function toSQL(): string
	{
		return parent::toSQL() . "drop default";
	}
}