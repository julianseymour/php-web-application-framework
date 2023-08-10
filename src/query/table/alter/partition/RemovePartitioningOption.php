<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class RemovePartitioningOption extends AlterOption
{

	public function toSQL(): string
	{
		return "remove partitioning";
	}
}