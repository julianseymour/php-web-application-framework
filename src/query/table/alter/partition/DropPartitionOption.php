<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class DropPartitionOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// DROP PARTITION partition_names
		return "drop partition " . implode(',', $this->getPartitionNames());
	}
}