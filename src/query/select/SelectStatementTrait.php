<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SelectStatementTrait
{

	protected $selectStatement;

	public function setSelectStatement(?SelectStatement $obj): ?SelectStatement
	{
		$f = __METHOD__; //"SelectStatementTrait(".static::getShortClass().")->setSelectStatement()";
		if ($obj == null) {
			unset($this->selectStatement);
			return null;
		} elseif (! $obj instanceof SelectStatement) {
			Debug::error("{$f} input parameter must be SelectStatement or null");
		}
		return $this->selectStatement = $obj;
	}

	public function hasSelectStatement(): bool
	{
		return isset($this->selectStatement) && $this->selectStatement instanceof SelectStatement;
	}

	public function getSelectStatement(): SelectStatement
	{
		$f = __METHOD__; //"SelectStatementTrait(".static::getShortClass().")->getSelectStatement()";
		if (! $this->hasSelectStatement()) {
			Debug::error("{$f} select statement is undefined");
		}
		return $this->selectStatement;
	}
}