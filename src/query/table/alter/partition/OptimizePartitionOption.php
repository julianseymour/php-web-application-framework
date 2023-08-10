<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class OptimizePartitionOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// OPTIMIZE PARTITION {partition_names | ALL}
		return "optimize partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all");
	}
}