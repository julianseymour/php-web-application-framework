<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use Exception;

trait ColumnExpressionsTrait
{

	protected $columnExpressionLists;

	protected function setColumnExpressionList($listname, ...$expressions)
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->setColumnExpressionList()";
		try{
			$print = false;
			if($expressions == null) {
				if($this->hasColumnExpressionList($listname)) {
					unset($this->columnExpressionLists[$listname]);
				}
				return null;
			}
			$argc = func_num_args();
			if($print) {
				Debug::print("{$f} {$argc} arguments passed to this function");
			}
			if($argc === 2) {
				if(is_array($expressions[0])) {
					if($print) {
						Debug::print("{$f} user passed an array of column expressions or names");
					}
					$expressions = $expressions[0];
				}elseif($print) {
					Debug::print("{$f} user definitely passed an unrolled list of column names");
				}
			}elseif($print) {
				Debug::print("{$f} user better have passed an unrolled list of column names");
			}
			if(!is_associative($expressions)) {
				$arr = [];
				foreach($expressions as $expr) {
					if(!is_string($expr)) {
						Debug::error("{$f} cannot reindex non-integer, non-associative array");
					}
					$arr[$expr] = '?';
				}
				$expressions = $arr;
			}elseif($print) {
				Debug::print("{$f} column expression array is already associative");
			}
			if($print) {
				Debug::printArray($expressions);
			}
			if(! static::validateColumnExpressionList($expressions)) {
				Debug::error("{$f} column expression list validation failed");
			}elseif(! isset($this->columnExpressionLists)) {
				$this->columnExpressionLists = [
					$listname => $expressions
				];
			}
			return $this->columnExpressionLists[$listname] = $expressions;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function getColumnExpressionListMember($listname, $name)
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->getColumnExpressionListMember()";
		if(!$this->hasColumnExpressionListMember($name)) {
			Debug::error("{$f} column \"{$name}\" is undefined");
		}
		return $this->columnExpressionLists[$listname][$name];
	}

	protected function hasColumnExpressionList($listname)
	{
		return $this->hasColumnExpressionLists() && array_key_exists($listname, $this->columnExpressionLists);
	}

	protected function hasColumnExpressionListMember($listname, $name)
	{
		return $this->hasColumnExpressionList($listname) && array_key_exists($name, $this->columnExpressionLists[$listname]);
	}

	protected function hasColumnExpressionLists()
	{
		return isset($this->columnExpressionLists) && is_array($this->columnExpressionLists) && ! empty($this->columnExpressionLists);
	}

	protected function getColumnExpressionList($listname)
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->getColumnExpressionList()";
		if(!$this->hasColumnExpressionList($listname)) {
			Debug::error("{$f} colun expression list \"{$listname}\" is undefined");
		}
		return $this->columnExpressionLists[$listname];
	}

	private static function validateColumnExpressionList($expressions)
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->validateColumnExpressionList()";
		if(!is_array($expressions)) {
			Debug::warning("{$f} this function accepts only associative arrays");
			return false;
		} else
			foreach($expressions as $name => $expr) {
				if(!is_string($name)) {
					Debug::warning("{$f} input parameter must be associative");
					return false;
				}elseif(empty($name)) {
					Debug::warning("{$f} cannot have empty string array index");
					return false;
				}elseif(is_string($expr)) {
					if($expr !== '?') {
						Debug::warning("{$f} don't stick actual values in here please");
						return false;
					}
				}elseif(!$expr instanceof ExpressionCommand) {
					Debug::warning("{$f} array values must be either ? or an expression command");
					return false;
				}
			}
		return true;
	}

	protected function mergeColumnExpressionList($listname, ...$expressions)
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->mergeColumnExpressionList()";
		if(! static::validateColumnExpressionList($expressions)) {
			Debug::error("{$f} column expression list validation failed");
		}elseif(!$this->hasColumnExpressionList($listname)) {
			return $this->setColumnExpressions($listname, ...$expressions);
		}
		$list = $this->getColumnExpressionList($listname);
		return $this->columnExpressionLists[$listname] = array_merge($list, $expressions);
	}

	protected function setColumnExpressionListMember($listname, $name, $expression)
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->setColumnExpressionListMember()";
		if(! static::validateColumnExpressionList([
			$name => $expression
		])) {
			Debug::error("{$f} column expression list validation failed");
		}elseif(!$this->hasColumnExpressionLists()) {
			$this->columnExpressionLists = [
				$listname => [
					$name => $expression
				]
			];
		}elseif(!$this->hasColumnExpressionList($listname)) {
			$this->setColumnExpressionList($listname, [
				$name => $expression
			]);
		}else{
			return $this->columnExpressionLists[$listname][$name] = $expression;
		}
		return $expression;
	}

	public function setColumnExpressions(...$expressions)
	{
		return $this->setColumnExpressionList(CONST_DEFAULT, ...$expressions);
	}

	public function getColumnExpression($name)
	{
		return $this->getColumnExpressionListMember(CONST_DEFAULT, $name);
	}

	public function getColumnExpressionCount()
	{
		return count($this->getColumnExpressions());
	}

	public function getColumnExpressions()
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->getColumnExpressions()";
		if(!$this->hasColumnExpressions()) {
			Debug::error("{$f} column expressions are undefined");
		}
		return $this->getColumnExpressionList(CONST_DEFAULT);
	}

	public function hasColumnExpressions()
	{
		return $this->hasColumnExpressionList(CONST_DEFAULT);
	}

	public function hasColumnExpression($name)
	{
		return $this->hasColumnExpressionListMember(CONST_DEFAULT, $name);
	}

	public function mergeColumnExpressions($expressions)
	{
		return $this->mergeColumnExpressionList(CONST_DEFAULT, $expressions);
	}

	public function setColumnExpression($name, $expression)
	{
		return $this->setColumnExpressionListMember(CONST_DEFAULT, $name, $expression);
	}

	public function withColumnExpressions($expressions)
	{
		$this->setColumnExpressions($expressions);
		return $this;
	}

	protected function getAssignmentListString($expressions, $alias = null)
	{
		$f = __METHOD__; //"ColumnExpressionsTrait(".static::getShortClass().")->getAssignmentListString()";
		$print = false;
		if(!is_array($expressions)) {
			Debug::error("{$f} first parameter must be an array");
		}elseif(empty($expressions)) {
			Debug::print("{$f} expressions array is empty");
			return null;
		}elseif(! static::validateColumnExpressionList($expressions)) {
			Debug::error("{$f} column expression list validation failed");
		}
		$string = "";
		$i = 0;
		foreach($expressions as $name => $expr) {
			if($i ++ > 0) {
				$string .= ",";
			}
			$string .= back_quote($name) . "=";
			if($alias !== null && is_string($alias) && ! empty($alias)) {
				$string .= "{$alias}.";
			}
			if($expr instanceof SQLInterface) {
				if($print) {
					Debug::print("{$f} expression is an SQL interface");
				}
				$expr = $expr->toSQL();
			}elseif(is_string($expr)) {
				if($print) {
					Debug::print("{$f} expression is the string \"{$expr}\"");
				}
				if($expr !== "?") {
					$expr = back_quote($expr);
				}
			}else{
				Debug::error("{$f} expression \"{$expr}\" is neither string nor SQL interface");
			}
			if($print) {
				Debug::print("{$f} appending expression \"{$expr}\"");
			}
			$string .= $expr;
		}
		if(empty($string)) {
			Debug::error("{$f} empty string");
		}elseif($print) {
			Debug::print("{$f} returning \"{$string}\"");
		}
		return $string;
	}
}
