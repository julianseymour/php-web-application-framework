<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\partition;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait PartitionedTrait
{

	use ArrayPropertyTrait;

	public function setPartitionDefinitions($partitionDefinitions)
	{
		$f = __METHOD__; //"PartitionedTrait(".static::getShortClass().")->setPartitionDefinitions()";
		foreach ($partitionDefinitions as $partitionDefinition) {
			if (! $partitionDefinition instanceof PartitionDefinition) {
				Debug::error("{$f} this function accepts only arrays of PartitionDefintions");
			}
		}
		return $this->setArrayProperty("partitionDefinitions", $partitionDefinitions);
	}

	public function pushPartitionDefinitions(...$partitionDefinitions)
	{
		$f = __METHOD__; //"PartitionedTrait(".static::getShortClass().")->pushPartitionDefinitions()";
		foreach ($partitionDefinitions as $partitionDefinition) {
			if (! $partitionDefinition instanceof PartitionDefinition) {
				Debug::error("{$f} this function accepts only arrays of PartitionDefintions");
			}
		}
		return $this->pushArrayProperty("partitionDefinitions", $partitionDefinitions);
	}

	public function withPartitionDefinitions($partitionDefinitions)
	{
		$this->setPartitionDefinitions($partitionDefinitions);
		return $this;
	}

	public function hasPartitionDefinitions()
	{
		return $this->hasArrayProperty("partitionDefinitions");
	}

	public function getPartitionDefinitions()
	{
		return $this->getProperty("partitionDefinitions");
	}

	public function mergePartitionDefinitions($partitionDefintions)
	{
		return $this->mergeArrayProperty("partitionDefinitions", $partitionDefintions);
	}
}
