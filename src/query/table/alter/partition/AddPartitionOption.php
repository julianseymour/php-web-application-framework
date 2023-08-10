<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\partition\PartitionDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class AddPartitionOption extends AlterOption
{

	protected $partitionDefinition;

	public function __construct($partitionDefinition)
	{
		parent::__construct();
		$this->setPartitionDefinition($partitionDefinition);
	}

	public function setPartitionDefinition($partitionDefinition)
	{
		$f = __METHOD__; //AddPartitionOption::getShortClass()."(".static::getShortClass().")->setPartitionDefinition()";
		if ($partitionDefinition == null) {
			unset($this->partitionDefinition);
			return null;
		} elseif (! $partitionDefinition instanceof PartitionDefinition) {
			Debug::error("{$f} this function accepts only null and PartitionDefintions");
		}
		return $this->partitionDefinition;
	}

	public function hasPartitionDefinition()
	{
		return isset($this->partitionDefinition);
	}

	public function getPartitionDefinition()
	{
		$f = __METHOD__; //AddPartitionOption::getShortClass()."(".static::getShortClass().")->getPartitionDefinition()";
		if (! $this->hasPartitionDefinition()) {
			Debug::error("{$f} partition definition is undefined");
		}
		return $this->partitionDefinition;
	}

	public function toSQL(): string
	{
		// ADD PARTITION (partition_definition)
		$partition = $this->getPartitionDefinition();
		if ($partition instanceof SQLInterface) {
			$partition = $partition->toSQL();
		}
		return "add partition ({$partition})";
	}
}