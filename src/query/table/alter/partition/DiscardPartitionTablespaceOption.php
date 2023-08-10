<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class DiscardPartitionTablespacePartition extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// DISCARD PARTITION {partition_names | ALL} TABLESPACE
		return "discard partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all") . " tablespace";
	}
}