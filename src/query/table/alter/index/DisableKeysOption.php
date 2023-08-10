	<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class DisableKeysOption extends AlterOption
{

	public function toSQL(): string
	{
		return "disable keys";
	}
}