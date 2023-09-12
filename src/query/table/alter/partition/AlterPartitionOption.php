<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class AlterPartitionOption extends AlterOption
{

	use MultiplePartitionNamesTrait;

	public function __construct($partitionNames = null)
	{
		parent::__construct();
		$this->requirePropertyType("partitionNames", "s");
		if($partitionNames !== null) {
			$this->setPartitionNames($partitionNames);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
	}
}
