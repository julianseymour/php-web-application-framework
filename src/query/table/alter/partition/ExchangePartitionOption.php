<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\ValidationTrait;

class ExchangePartitionOption extends AlterOption
{

	use TableNameTrait;
	use ValidationTrait;

	protected $partitionName;

	public function __construct($partitionName, $tableName, $validate = null)
	{
		parent::__construct();
		$this->setPartitionName($partitionName);
		$this->setTableName($tableName);
		if($validate !== null) {
			$this->setValidation($validate);
		}
	}

	public function setPartitionName($partitionName)
	{
		$f = __METHOD__; //ExchangePartitionOption::getShortClass()."(".static::getShortClass().")->setPartitionName()";
		if($partitionName == null) {
			unset($this->partitionName);
			return null;
		}elseif(!is_string($this->partitionName)) {
			Debug::error("{$f} partition name is not a string");
		}
		return $this->partitionName = $partitionName;
	}

	public function hasPartitionName()
	{
		return isset($this->partitionName) && is_string($this->partitionName) && ! empty($this->partitionName);
	}

	public function getPartitionName()
	{
		$f = __METHOD__; //ExchangePartitionOption::getShortClass()."(".static::getShortClass().")->getPartitionName()";
		if(!$this->hasPartitionName()) {
			Debug::error("{$f} partiton name is undefined");
		}
		return $this->partitionName;
	}

	public function toSQL(): string
	{
		// EXCHANGE PARTITION partition_name WITH TABLE tbl_name [{WITH | WITHOUT} VALIDATION]
		$string = "exchange partition " . $this->getPartitionName() . " with table ";
		if($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName()) . ($this->validate !== null ? "with" . (! $this->getValidation() ? "out" : "") . " validation" : "");
		return $string;
	}
}