<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ColumnNameTrait{

	protected $columnName;

	/**
	 * do not add parameter/return types -- ColumnValueCommand needs to be able to evaluate column names
	 *
	 * @param string|ValueReturningCommandInterface|NULL $columnName
	 * @return NULL|string|ValueReturningCommandInterface
	 */
	public function setColumnName($columnName){
		if($this->hasColumnName()){
			$this->release($this->columnName);
		}
		return $this->columnName = $this->claim($columnName);
	}

	public function hasColumnName(): bool{
		return isset($this->columnName);
	}

	public function getColumnName(){
		$f = __METHOD__;
		if(!$this->hasColumnName()){
			Debug::error("{$f} column name is undefined for this ".$this->getDebugString());
		}
		return $this->columnName;
	}

	public function withColumnName($name){
		$this->setColumnName($name);
		return $this;
	}
}