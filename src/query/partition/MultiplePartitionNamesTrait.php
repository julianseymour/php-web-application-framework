<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\partition;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultiplePartitionNamesTrait
{

	use ArrayPropertyTrait;

	public function setPartitionNames($partitionNames)
	{
		return $this->setArrayProperty("partitionNames", $partitionNames);
	}

	public function pushPartitionNames(...$partitionNames)
	{
		return $this->pushArrayProperty("partitionNames", ...$partitionNames);
	}

	public function hasPartitionNames()
	{
		return $this->hasArrayProperty("partitionNames");
	}

	public function getPartitionNames()
	{
		return $this->getProperty("partitionNames");
	}

	public function mergePartitionNames($partitionNames)
	{
		return $this->mergeArrayProperty("partitionNames", $partitionNames);
	}
}
