<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class CheckPartitionOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// CHECK PARTITION {partition_names | ALL}
		return "check partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all");
	}
}