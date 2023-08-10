<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\where;

use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

interface WhereConditionalInterface extends SQLInterface
{

	/**
	 * returns a flat array of WhereConditions (i.e.
	 * not parameters of a VariadicExpressionCommand)
	 *
	 * @return array|NULL
	 */
	public function getFlatWhereConditionArray(): ?array;

	/**
	 * The difference between flat and superflat:
	 * getSuperflatWhereConditionArray flattens WhereConditions with select statement parameters;
	 * getFlatWhereConditionArray does not.
	 *
	 * @return array|NULL
	 */
	public function getSuperflatWhereConditionArray(): ?array;

	/**
	 * Returns SUCCESS if the WhereCondition has not been marked as failed, FAILURE otherwise
	 *
	 * @return int
	 */
	public function audit(): int;

	/**
	 * Returns the number of parameters required to prepare/bind/execute this statement/condition.
	 * Mostly useful for debugging.
	 *
	 * @return int
	 */
	// public function getRequiredParameterCount():int;

	/**
	 * Formats AndCommand to use AND instead of &&
	 */
	// public function mySQLFormat();

	/**
	 * returns column names of all WhereConditions
	 *
	 * @return array
	 */
	// public function getColumnNames():array;

	/**
	 * returns only the column names of conditions with bindable variables
	 */
	public function getConditionalColumnNames(): array;
}
