<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\where;

use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

interface WhereConditionalInterface extends SQLInterface{

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
	 * returns only the column names of conditions with bindable variables
	 */
	public function getConditionalColumnNames(): array;
}
