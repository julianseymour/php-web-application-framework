<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\where;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::deprecated(__FILE__);

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
class LazyWhereCondition extends WhereCondition{
	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"disabled"
		]);
	}

	public function setDisabledFlag($value = true){
		return $this->setFlag("disabled", $value);
	}

	public function getDisabledFlag(){
		return $this->getFlag("disabled");
	}

	public function disable(){
		$this->setDisabledFlag(true);
		return $this;
	}

	public function getPreliminaryQueryResults($mysqli){
		$f = __METHOD__;
		$query = $this->getSelectStatement();
		$result = $query->executeGetResult($mysqli);
		$count = $result->num_rows;
		if ($count == 0) {
			Debug::error("{$f} 0 results");
		}
		$this->setParameterCount($count);
		return $result->fetch_all(MYSQLI_ASSOC);
	}

	public function toSQL(): string{
		$f = __METHOD__;
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
}
