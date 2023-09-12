<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\column;

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
		if($columnName === null) {
			unset($this->columnName);
			return null;
		}
		return $this->columnName = $columnName;
	}

	public function hasColumnName(): bool{
		return isset($this->columnName);
	}

	public function getColumnName(){
		$f = __METHOD__;
		if(!$this->hasColumnName()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} column name is undefined. Declared {$decl}");
		}
		return $this->columnName;
	}

	public function withColumnName($name){
		$this->setColumnName($name);
		return $this;
	}
}