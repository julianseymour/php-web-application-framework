<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\where;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

/**
 * WhereCondition that depends on the result of another query to generate.
 * Useful to perform queries against rows that match a foreign key stored separately in an intersection table.
 * You must define the select statement and type specifier for the column that is returned by this query.
 *
 * @author j
 *        
 */
class LazyWhereCondition extends WhereCondition
{

	// use SelectStatementTrait;

	/*
	 * public function __construct($varname, $operator, $typedef, $selectStatement){
	 * parent::__construct($varname, $operator, $typedef);
	 * $this->setSelectStatement($selectStatement);
	 * }
	 */
	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			"disabled"
		]);
	}

	public function setDisabledFlag($value = true)
	{
		return $this->setFlag("disabled", $value);
	}

	public function getDisabledFlag()
	{
		return $this->getFlag("disabled");
	}

	public function disable()
	{
		$this->setDisabledFlag(true);
		return $this;
	}

	public function getPreliminaryQueryResults($mysqli)
	{
		$f = __METHOD__; //LazyWhereCondition::getShortClass()."(".static::getShortClass().")->getPreliminaryQueryResults()";
		$query = $this->getSelectStatement();
		$result = $query->executeGetResult($mysqli);
		$count = $result->num_rows;
		if ($count == 0) {
			Debug::error("{$f} 0 results");
		}
		$this->setParameterCount($count);
		return $result->fetch_all(MYSQLI_ASSOC);
	}

	/*
	 * public function inferParameterCount():int{
	 * $f = __METHOD__; //LazyWhereCondition::getShortClass()."(".static::getShortClass().")->inferParameterCount()";
	 * if(!$this->hasParameterCount()){
	 * $operator = $this->getOperator();
	 * switch($operator){
	 * case OPERATOR_IN:
	 * case OPERATOR_NOT_IN:
	 * return $this->getSelectStatement()->inferParameterCount();
	 * default:
	 * }
	 * }
	 * return $this->getParameterCount();
	 * }
	 */

	/*
	 * public function getSuperflatWhereConditionArray():?array{
	 * return $this->getSelectStatement()->getSuperflatWhereConditionArray();
	 * }
	 */
	public function toSQL(): string
	{
		$f = __METHOD__; //LazyWhereCondition::getShortClass()."(".static::getShortClass().")->toSQL()";
		try {
			if ($this->getDisabledFlag()) {
				$cn = $this->getColumnName();
				$ts = $this->getTypeSpecifier();
				$and = new AndCommand(new WhereCondition($cn, OPERATOR_IS_NULL, $ts), new WhereCondition($cn, OPERATOR_IS_NOT_NULL, $ts));
				return $and->toSQL();
			}
			return parent::toSQL();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function audit(): int
	{
		return $this->getDisabledFlag() ? FAILURE : SUCCESS;
	}

	/*
	 * public function mySQLFormat(){
	 * if(!$this->hasSelectStatement()){
	 * return SUCCESS;
	 * }
	 * return $this->getSelectStatement()->mySQLFormat();
	 * }
	 */
}
