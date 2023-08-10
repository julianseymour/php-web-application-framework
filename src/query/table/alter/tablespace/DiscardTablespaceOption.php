<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\tablespace;

use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class DiscardTablespaceOption extends AlterOption
{

	public function toSQL(): string
	{
		return "discard tablespace";
	}
}