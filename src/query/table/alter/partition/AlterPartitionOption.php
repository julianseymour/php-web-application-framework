<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class AlterPartitionOption extends AlterOption{

	use MultiplePartitionNamesTrait;

	public function __construct($partitionNames = null){
		parent::__construct();
		$this->requirePropertyType("partitionNames", "s");
		if($partitionNames !== null){
			$this->setPartitionNames($partitionNames);
		}
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->propertyTypes, $deallocate);
	}
}
