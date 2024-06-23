<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use JulianSeymour\PHPWebApplicationFramework\query\partition\PartitionDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\partition\PartitionedTrait;

class ReorganizePartitionOption extends AlterPartitionOption{

	use PartitionedTrait;

	public function __construct($partitionNames=null, $partitionDefinitions=null){
		parent::__construct($partitionNames);
		$this->requirePropertyType("partitionDefinitions", PartitionDefinition::class);
		if($partitionDefinitions !== null){
			$this->setPartitionDefintions($partitionDefinitions);
		}
	}

	public function toSQL(): string{
		// REORGANIZE PARTITION partition_names INTO (partition_definitions)
		return "reorganize partition " . ($this->hasPartitionNames() ? implode(',', $this->getPartitionNames()) : "all") . " into (" . implode($this->getPartitionDefinitions()) . ")";
	}
	
	
}