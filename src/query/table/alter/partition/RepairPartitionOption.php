<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class RepairPartitionOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// REPAIR PARTITION {partition_names | ALL}
		return "repair partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all");
	}
}