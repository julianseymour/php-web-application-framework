<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class AnalyzePartitionOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// ANALYZE PARTITION {partition_names | ALL}
		return "analyze partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all");
	}
}