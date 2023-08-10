<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class DropPrimaryKeyOption extends AlterOption
{

	public function toSQL(): string
	{
		return "drop primary key";
	}
}
