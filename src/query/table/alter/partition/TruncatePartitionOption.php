<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class TruncatePartitionOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// TRUNCATE PARTITION {partition_names | ALL}
		return "truncate partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all");
	}
}