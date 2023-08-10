<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint;

class DropConstraintOption extends SymbolicConstraintOption
{

	public function toSQL(): string
	{
		return "drop" . parent::toSQL();
	}
}
