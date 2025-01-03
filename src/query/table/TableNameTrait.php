<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\validate_table_name;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CommonTableExpression;
use JulianSeymour\PHPWebApplicationFramework\query\join\TableFactor;

trait TableNameTrait{

	protected $tableName;

	public function setTableName(?string $tableName):?string{
		$f = __METHOD__;
		$print = false;
		if($tableName instanceof TableFactor){
			if($print){
				Debug::print("{$f} table name is the table factor \"{$tableName}\"");
			}
			// ok
		}elseif($tableName instanceof CommonTableExpression){
			return $this->setTableName($tableName->getName());
		}elseif(!validate_table_name($tableName)){
			Debug::error("{$f} invalid table name \"{$tableName}\"");
			return $this->setObjectStatus(ERROR_INVALID_TABLE_NAME);
		}
		if($this->hasTableName()){
			$this->release($this->tableName);
		}
		return $this->tableName = $this->claim($tableName);
	}

	public function hasTableName():bool{
		return !empty($this->tableName);
	}

	public function getTableName(): string{
		$f = __METHOD__;
		if(!$this->hasTableName()){
			Debug::error("{$f} full table name is undefined");
		}
		return $this->tableName;
	}

	public function withTableName(?string $tableName):object{
		$this->setTableName($tableName);
		return $this;
	}
}