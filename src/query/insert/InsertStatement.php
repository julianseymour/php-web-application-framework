<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\insert;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AliasTrait;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\release;

class InsertStatement extends AbstractInsertStatement{

	use AliasTrait;

	public function __construct(){
		parent::__construct();
		$this->requirePropertyType("columnAliases", 's');
	}

	public function setColumnAliases($values){
		return $this->setArrayProperty("columnAliases", $values);
	}

	public function hasColumnAliases():bool{
		return $this->hasArrayProperty("columnAliases");
	}

	public function getColumnAliases(){
		$f = __METHOD__;
		if(!$this->hasColumnAliases()){
			Debug::error("{$f} column aliases are undefined");
		}
		return $this->getProperty("columnAliases");
	}

	public function pushColumnAliases(...$values){
		return $this->pushArrayProperty("columnAliases", ...$values);
	}

	public function mergeColumnAliases($values){
		return $this->mergeArrayProperty("columnAliases", $values);
	}

	public function withColumnAliases($values):InsertStatement{
		$this->setColumnAliases($values);
		return $this;
	}

	public function setDuplicateColumnExpressions(...$expressions){
		return $this->setArrayProperty('duplicate', ...$expressions);
	}

	public function hasDuplicateColumnExpressions():bool{
		return $this->hasArrayProperty('duplicate');
	}

	public function hasDuplicateColumnExpression($name):bool{
		return $this->hasArrayPropertyKey('duplicate', $name);
	}

	public function getDuplicateColumnExpressions(){
		return $this->getProperty('duplicate');
	}

	public function getDuplicateColumnExpression($name){
		return $this->getArrayPropertyValue('duplicate', $name);
	}

	public function setDuplicateColumnExpression($name, $expression){
		return $this->setArrayPropertyValue('duplicate', $name, $expression);
	}

	public function withDuplicateColumnExpressions($expressions){
		$this->setDuplicateColumnExpressions($expressions);
		return $this;
	}

	public function getQueryStatementString(): string{
		$f = __METHOD__;
		try{
			// INSERT
			$string = "insert ";
			$string .= $this->getInsertQueryStatementString();
			$alias = $this->hasAlias() ? $this->getAlias() : null;
			// [AS row_alias[(col_alias [, col_alias] ...)]]
			if($this->hasAlias()){
				$string .= " as " . $alias;
				if($this->hasColumnAliases()){
					$string .= "(" . implode(',', $this->getColumnAliases()) . ")";
				}
			}
			$string .= $this->getValueAssignmentString();
			// [ON DUPLICATE KEY UPDATE assignment_list]
			if($this->hasDuplicateColumnExpressions()){
				$string .= " on duplicate key update " . $this->getAssignmentListString($this->getDuplicateColumnExpressions(), $alias);
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->alias, $deallocate);
	}
}
