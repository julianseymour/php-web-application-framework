<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\tablespace;

use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class ImportTablespaceOption extends AlterOption
{

	public function toSQL(): string
	{
		return "import tablespace";
	}
}