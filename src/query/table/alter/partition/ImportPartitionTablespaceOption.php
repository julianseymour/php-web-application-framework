<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

class ImportPartitionTablespaceOption extends AlterPartitionOption
{

	public function toSQL(): string
	{
		// IMPORT PARTITION {partition_names | ALL} TABLESPACE
		return "import partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all") . " tablespace";
	}
}