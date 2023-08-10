<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint;

class DropForeignKeyOption extends DropConstraintOption
{

	public function toSQL(): string
	{
		return "drop foreign key " . $this->getSymbol();
	}
}
