<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\where;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\LimitOffsetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;
use Exception;
use mysqli;
use mysqli_result;
use mysqli_sql_exception;

abstract class WhereConditionalStatement extends QueryStatement implements CacheableInterface, WhereConditionalInterface{

	use CacheableTrait;
	use LimitOffsetTrait;
	use FullTableNameTrait;

	/**
	 * Either a WhereCondition or VariadicExpression with WhereCondition/VariadicExpression params
	 *
	 * @var WhereConditionalInterface
	 */
	protected $whereCondition;

	public function where(...$wc): WhereConditionalStatement{
		$this->setWhereCondition(...$wc);
		return $this;
	}

	public function inferParameterCount(){
		if(!$this->hasParameters()) {
			$count = 0;
			$wheres = $this->getFlatWhereConditionArray();
			if(!empty($wheres)) {
				foreach($wheres as $where) {
					$count += $where->inferParameterCount();
				}
			}
			return $count;
		}
		return $this->getParameterCount();
	}

	public function getConditionalColumnNames(): array{
		return $this->getWhereCondition()->getConditionalColumnNames();
	}

	/**
	 * Set the expression for generating the WHERE clause.
	 * Accepts any of the following:
	 * WhereConditionalInterface (preferred)
	 * WhereConditionalInterface, WhereConditionalInterface ... (gets ANDed together)
	 * WhereConditionalInterface[] (same as above)
	 * string (gets treated as a column name for a = WhereCondition)
	 * string, string, ... (same as above, then gets anded together)
	 * string[] (same as above)
	 *
	 * @param mixed ...$conditions
	 * @return WhereConditionalInterface|AndCommand|WhereCondition
	 */
	public function setWhereCondition(...$conditions){
		$f = __METHOD__;
		try{
			$print = false;
			$count = count($conditions);
			if($count === 0) {
				Debug::error("{$f} undefined where condition");
			}elseif($count > 1 || is_array($conditions[0])) {
				if(is_array($conditions[0])) {
					$conditions = $conditions[0];
				}
				if(count($conditions) === 1 && $conditions[0] instanceof WhereConditionalInterface) {
					if($print) {
						Debug::print("{$f} array has 1 member and it's a WhereConditionalInterface");
					}
					return $this->whereCondition = $conditions[0];
				}elseif($print) {
					Debug::print("{$f} array has more than one member, or it has something that isn't a WhereConditionalInterface");
				}
				foreach($conditions as $i => $condition) {
					if(is_array($condition)) {
						Debug::error("{$f} multidimensional array");
					}elseif(is_string($condition)) {
						$conditions[$i] = new WhereCondition($condition, OPERATOR_EQUALS);
					}elseif(!$condition instanceof WhereConditionalInterface) {
						$gottype = is_object($condition) ? $condition->getClass() : gettype($condition);
						Debug::error("{$f} item at position {$i} is a {$gottype}");
					}
				}
				$whereCondition = new AndCommand($conditions);
			}elseif(is_string($conditions[0])) {
				$whereCondition = new WhereCondition($conditions[0], OPERATOR_EQUALS);
			}elseif($conditions[0] instanceof WhereConditionalInterface || $conditions[0] instanceof BinaryExpressionCommand) {
				$whereCondition = $conditions[0];
			}else{
				Debug::error("{$f} none of the above");
			}
			if($print) {
				Debug::print("{$f} assigning WhereCondition \"{$whereCondition}\"");
			}
			return $this->whereCondition = $whereCondition;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getWhereCondition(){
		$f = __METHOD__;
		if(!$this->hasWhereCondition()) {
			Debug::error("{$f} where condition is undefined");
		}
		return $this->whereCondition;
	}

	public function getFlatWhereConditionArray(): ?array{
		if(!$this->hasWhereCondition()) {
			return null;
		}
		return $this->getWhereCondition()->getFlatWhereConditionArray();
	}

	public function getSuperflatWhereConditionArray(): ?array{
		if(!$this->hasWhereCondition()) {
			return null;
		}
		return $this->getWhereCondition()->getSuperflatWhereConditionArray();
	}

	public function hasWhereCondition(): bool{
		return isset($this->whereCondition);
	}

	public function pushWhereConditionParameters(...$parameters){
		$f = __METHOD__; 
		$print = false;
		if(!$this->hasWhereCondition()) {
			Debug::error("{$f} don't call this unless the QueryStatement already has a WhereCondition");
		}
		$wc = $this->getWhereCondition();
		if($wc instanceof AndCommand) {
			if($print) {
				Debug::print("{$f} where condition is an AND expression -- pushing additional parameters");
			}
			return $wc->pushParameters(...$parameters);
		}elseif($print) {
			Debug::print("{$f} where condition already exists, and it's not an AND expression -- creating one now");
		}
		$and = new AndCommand($wc, ...$parameters);
		// $and->mySQLFormat();
		if($print) {
			Debug::print("{$f} new where condition is \"{$and}\"");
		}
		return $this->setWhereCondition($and);
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param string $typedef
	 * @param mixed[] $params
	 * @return NULL|mysqli_result
	 */
	public function prepareBindExecuteGetResult($mysqli, $typedef, ...$params){
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			if(empty($mysqli)) {
				Debug::error("{$f} mysqli object is undefined");
			}elseif(!$mysqli instanceof mysqli) {
				Debug::error("{$f} mysqli object is not a mysqli");
			}elseif($mysqli->connect_errno) {
				Debug::warning("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return db()->rollbackTransaction($mysqli);
			}elseif(!$mysqli->ping()) {
				Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return db()->rollbackTransaction($mysqli);
			}elseif(empty($params)) {
				Debug::printStackTraceNoExit("{$f} don't use this function unless there are parameters to bind");
				return $this->executeGetResult($mysqli);
			} else
				foreach($params as $param) {
					if(is_array($param)) {
						if(count($params) === 1) {
							$params = $param[0];
						}
						$decl = $this->getDeclarationLine();
						Debug::error("{$f} you forgot to unroll the parameters. Instantiated {$decl}");
					}
				}

			if(cache()->enabled() && QUERY_CACHE_ENABLED && cache()->has($this)) {
				if($print) {
					Debug::print("{$f} this statement's results have been cached");
				}
				return cache()->get($this);
			}elseif($print) {
				Debug::print("{$f} this statement's results have not been cached; about to prepare, bind and execute statement \'{$this}\" with type specifier \"{$typedef}\" and the following parameters");
				Debug::printArray($params);
			}
			$st = $this->prepareBindExecuteGetStatement($mysqli, $typedef, ...$params);
			if($st === null) {
				if($print) {
					Debug::error("{$f} executed statement returned null -- this is only permissible in the case of a failed WhereCondition evaluation");
				}
				return null;
			}elseif($result = $st->get_result()) {
				if($print) {
					Debug::print("{$f} successfully got result of prepared query statement {$this}");
				}
				if($result->num_rows > 0) {
					if($this->isCacheable() && QUERY_CACHE_ENABLED) {
						if($print) {
							Debug::print("{$f} updating cache");
						}
						cache()->set($this->getCacheKey(), $result->fetch_all(MYSQLI_ASSOC), time() + 30 * 60);
					}elseif($print) {
						Debug::print("{$f} skipping cache update");
					}
				}elseif($print) {
					Debug::print("{$f} no results for query {$this} with the following parameters:");
					Debug::printArray($params);
				}
				return $result;
			}
			Debug::error("{$f} failed to get result of prepared query statement: \"{$st->error}\"");
			$this->setObjectStatus(ERROR_MYSQL_RESULT);
			return db()->rollbackTransaction($mysqli);
		}catch(mysqli_sql_exception $x) {
			x($f, $x);
		}
	}

	/**
	 * Calls prepareBindExecuteGetResult on queries with predefined type definition strings and parameter lists.
	 * Also useful for statements that have no variables to bind whatsoever, but return a result
	 *
	 * @param mysqli $mysqli
	 * @param string $query
	 * @return mysqli_result
	 */
	public function executeGetResult($mysqli){
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			if($this->hasParameters() && $this->hasTypeSpecifier()) {
				$typedef = $this->getTypeSpecifier();
				$params = $this->getParameters();
				if($print) {
					Debug::print("{$f} parameters are bundled with the query; about to call prepareBindExecuteGetResult() with type specifier {$typedef}");
					Debug::printArray($params);
				}
				return $this->prepareBindExecuteGetResult($mysqli, $typedef, ...$params);
			}elseif($print) {
				Debug::print("{$f} this statement lacks parameters and/or a type specifier");
			}
			return parent::executeGetResult($mysqli);
		}catch(mysqli_sql_exception $x) {
			x($f, $x);
		}
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param string $typedef
	 * @param mixed[] $params
	 * @return NULL|int
	 */
	public function prepareBindExecuteGetResultCount(mysqli $mysqli, string $typedef, ...$params): ?int{
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			if($print) {
				Debug::print("{$f} about to get result count for query \"{$this}\" with type specifier string \"{$typedef}\" and the following parameters:");
				Debug::printArray($params);
			}

			if(cache()->enabled() && QUERY_CACHE_ENABLED && cache()->has($this)) {
				return count(cache()->get($this));
			}
			$st = $this->prepareBindExecuteGetStatement($mysqli, $typedef, ...$params);
			if($st == null) {
				Debug::warning("{$f} executed statement returned null");
				db()->rollbackTransaction($mysqli);
				return 0;
			}elseif($print) {
				Debug::print("{$f} successfully executed prepared query statement");
			}
			if($result = $st->get_result()) {
				if($print) {
					Debug::print("{$f} successfully got result of prepared query statement");
				}
				$count = $result->num_rows;
				$st->free_result();
				$this->setObjectStatus(SUCCESS);
				if($print) {
					Debug::print("{$f} returning {$count}");
				}
				return $count;
			}elseif($print) {
				Debug::warning("{$f} failed to get result of prepared query statement: \"{$st->error}\"");
			}
			$this->setObjectStatus(ERROR_MYSQL_RESULT);
			db()->rollbackTransaction($mysqli);
			return 0;
		}catch(mysqli_sql_exception $x) {
			x($f, $x);
		}
	}
}
