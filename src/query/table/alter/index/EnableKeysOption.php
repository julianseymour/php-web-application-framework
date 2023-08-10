<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class EnableKeysOption extends AlterOption
{

	public function toSQL(): string
	{
		return "enable keys";
	}
}