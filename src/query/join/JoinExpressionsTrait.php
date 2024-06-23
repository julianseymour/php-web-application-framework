<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\join;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait JoinExpressionsTrait{

	use ArrayPropertyTrait;

	public function setJoinExpressions($joinExpressions){
		return $this->setArrayProperty("joinExpressions", $joinExpressions);
	}

	public function hasJoinExpressions(){
		return $this->hasArrayProperty("joinExpressions");
	}

	/**
	 *
	 * @return JoinExpression[]
	 */
	public function getJoinExpressions(){
		return $this->getProperty("joinExpressions");
	}

	public function pushJoinExpressions(...$joinExpressions){
		return $this->pushArrayProperty("joinExpressions", ...$joinExpressions);
	}

	public function mergeJoinExpressions($joinExpressions){
		return $this->mergeArrayProperty("joinExpressions", $joinExpressions);
	}

	public function withJoinExpressions(...$joinExpressions){
		$this->setJoinExpressions($joinExpressions);
		return $this;
	}

	public function getJoinExpressionString(): string{
		$f = __METHOD__;
		if(!$this->hasJoinExpressions()){
			Debug::error("{$f} join expressions are undefined");
		}
		return implode(",", $this->getJoinExpressions());
	}

	public function getJoinExpressionCount():int{
		return $this->getArrayPropertyCount("joinExpressions");
	}

	public function join($join_expression){
		$joined = JoinedTable::join($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function crossJoin($join_expression){
		$joined = JoinedTable::crossJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function innerJoin($join_expression){
		$joined = JoinedTable::innerJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function straightJoin($join_expression){
		$joined = JoinedTable::straightJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function leftJoin($join_expression){
		$joined = JoinedTable::leftJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function rightJoin($join_expression){
		$joined = JoinedTable::rightJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function naturalJoin($join_expression){
		$joined = JoinedTable::naturalJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function naturalInnerJoin($join_expression){
		$joined = JoinedTable::naturalInnerJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function naturalLeftJoin($join_expression){
		$joined = JoinedTable::naturalLeftJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}

	public function naturalRightJoin($join_expression){
		$joined = JoinedTable::naturalRightJoin($join_expression);
		$this->pushJoinExpressions($joined);
		return $joined;
	}
}
