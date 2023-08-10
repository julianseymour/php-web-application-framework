<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class RebuildPartitionOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// REBUILD PARTITION {partition_names | ALL}
		return "rebuild partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all");
	}
}