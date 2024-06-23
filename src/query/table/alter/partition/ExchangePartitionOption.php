<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\ValidationTrait;

class ExchangePartitionOption extends AlterOption{

	use FullTableNameTrait;
	use ValidationTrait;

	protected $partitionName;

	public function __construct($partitionName=null, $dbName=null, $tableName=null, $validate = null){
		parent::__construct();
		if($partitionName !== null){
			$this->setPartitionName($partitionName);
		}
		if($dbName !== null){
			$this->setDatabaseName($dbName);
		}
		if($tableName !== null){
			$this->setTableName($tableName);
		}
		if($validate !== null){
			$this->setValidation($validate);
		}
	}

	public function setPartitionName($partitionName){
		$f = __METHOD__;
		if(!is_string($this->partitionName)){
			Debug::error("{$f} partition name is not a string");
		}elseif($this->hasPartitionName()){
			$this->release($this->partitionName);
		}
		return $this->partitionName = $this->claim($partitionName);
	}

	public function hasPartitionName():bool{
		return isset($this->partitionName) && is_string($this->partitionName) && !empty($this->partitionName);
	}

	public function getPartitionName(){
		$f = __METHOD__;
		if(!$this->hasPartitionName()){
			Debug::error("{$f} partiton name is undefined");
		}
		return $this->partitionName;
	}

	public function toSQL(): string{
		// EXCHANGE PARTITION partition_name WITH TABLE tbl_name [{WITH | WITHOUT} VALIDATION]
		$string = "exchange partition " . $this->getPartitionName() . " with table ";
		if($this->hasDatabaseName()){
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName()) . ($this->validate !== null ? "with" . (! $this->getValidation() ? "out" : "") . " validation" : "");
		return $string;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->databaseName, $deallocate);
		$this->release($this->partitionName, $deallocate);
		$this->release($this->tableName, $deallocate);
		$this->release($this->validate, $deallocate);
	}
}