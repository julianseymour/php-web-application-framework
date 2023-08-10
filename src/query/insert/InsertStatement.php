<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\insert;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AliasTrait;
use Exception;

class InsertStatement extends AbstractInsertStatement
{

	use AliasTrait;

	public function __construct()
	{
		parent::__construct();
		$this->requirePropertyType("columnAliases", 's');
	}

	public function setColumnAliases($values)
	{
		return $this->setArrayProperty("columnAliases", $values);
	}

	public function hasColumnAliases()
	{
		return $this->hasArrayProperty("columnAliases");
	}

	public function getColumnAliases()
	{
		$f = __METHOD__; //InsertStatement::getShortClass()."(".static::getShortClass().")->getColumnAliases()";
		if (! $this->hasColumnAliases()) {
			Debug::error("{$f} column aliases are undefined");
		}
		return $this->getProperty("columnAliases");
	}

	public function pushColumnAliases(...$values)
	{
		return $this->pushArrayProperty("columnAliases", ...$values);
	}

	public function mergeColumnAliases($values)
	{
		return $this->mergeArrayProperty("columnAliases", $values);
	}

	public function withColumnAliases($values)
	{
		$this->setColumnAliases($values);
		return $this;
	}

	public function setDuplicateColumnExpressions(...$expressions)
	{
		return $this->setColumnExpressionList('duplicate', ...$expressions);
	}

	public function hasDuplicateColumnExpressions()
	{
		return $this->hasColumnExpressionList('duplicate');
	}

	public function hasDuplicateColumnExpression($name)
	{
		return $this->hasColumnExpressionListMember('duplicate', $name);
	}

	public function getDuplicateColumnExpressions()
	{
		return $this->getColumnExpressionList('duplicate');
	}

	public function getDuplicateColumnExpression($name)
	{
		return $this->getColumnExpressionListMember('duplicate', $name);
	}

	/*
	 * public function mergeDuplicateColumnExpressions($expressions){
	 * return $this->mergeColumnExpressionList('duplicate', $expressions);
	 * }
	 */
	public function setDuplicateColumnExpression($name, $expression)
	{
		return $this->setColumnExpressionListMember('duplicate', $name, $expression);
	}

	public function withDuplicateColumnExpressions($expressions)
	{
		$this->setDuplicateColumnExpressions($expressions);
		return $this;
	}

	public function getQueryStatementString(): string
	{
		$f = __METHOD__; //InsertStatement::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		try {
			// INSERT
			$string = "insert ";
			$string .= $this->getInsertQueryStatementString();
			$alias = $this->hasAlias() ? $this->getAlias() : null;
			// [AS row_alias[(col_alias [, col_alias] ...)]]
			if ($this->hasAlias()) {
				$string .= " as " . $alias;
				if ($this->hasColumnAliases()) {
					$string .= "(" . implode(',', $this->getColumnAliases()) . ")";
				}
			}
			$string .= $this->getValueAssignmentString();
			// [ON DUPLICATE KEY UPDATE assignment_list]
			if ($this->hasDuplicateColumnExpressions()) {
				$string .= " on duplicate key update " . $this->getAssignmentListString($this->getDuplicateColumnExpressions(), $alias);
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->alias);
	}
}
