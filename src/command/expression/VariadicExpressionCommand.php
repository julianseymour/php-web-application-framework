<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ComputerLanguageTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use JulianSeymour\PHPWebApplicationFramework\search\MatchFunction;
use Exception;

abstract class VariadicExpressionCommand extends ExpressionCommand implements JavaScriptInterface, SQLInterface, StringifiableInterface{

	use ComputerLanguageTrait;
	use ParametricTrait;

	public function __construct(...$parameters){
		$f = __METHOD__;
		parent::__construct();
		if(isset($parameters) && count($parameters) > 0) {
			if(count($parameters) === 1 && is_array($parameters[0])) {
				$parameters = $parameters[0];
			}
			foreach($parameters as $i => $p) {
				if(is_array($p)) {
					Debug::printArray($parameters);
					Debug::error("{$f} array parameter at index {$i}");
				}
			}
			$this->setParameters($parameters);
		}
	}

	public function hasMatchFunction(): bool{
		foreach($this->getFlatWhereConditionArray() as $wc) {
			if($wc instanceof MatchFunction) {
				return true;
			}
		}
		return false;
	}

	public function toSQL(): string{
		$this->setComputerLanguage(COMPUTER_LANGUAGE_SQL);
		return $this->__toString();
	}

	public function toJavaScript(): string{
		$this->setComputerLanguage(COMPUTER_LANGUAGE_JAVASCRIPT);
		return $this->__toString();
	}

	public function __toString(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$cl = $this->getComputerLanguage();
			if($this->getParameterCount() === 1) {
				$params = $this->getParameters();
				$param = $this->getParameter(array_keys($params)[0]);
				if(is_array($param)) {
					Debug::error("{$f} parameter is an array");
				}
				switch ($cl) {
					case COMPUTER_LANGUAGE_JAVASCRIPT:
						return $param->toJavaScript();
					case COMPUTER_LANGUAGE_SQL:
						return $param->toSQL();
					default:
						return $param->__toString();
				}
			}
			$operator = $this->getOperator();
			$string = "";
			foreach($this->getParameters() as $param) {
				if($string !== "") {
					$string .= " {$operator} ";
				}
				if($param instanceof ValueReturningCommandInterface) {
					if($param instanceof ExpressionCommand) {
						$param->setEscapeType(ESCAPE_TYPE_PARENTHESIS);
					}
					switch ($cl) {
						case COMPUTER_LANGUAGE_JAVASCRIPT:
							$string .= $param->toJavaScript();
							break;
						case COMPUTER_LANGUAGE_SQL:
							$string .= $param->toSQL();
							break;
						default:
							$string .= $param;
					}
				}elseif($param === null) {
					if($print) {
						Debug::print("{$f} argument is null");
					}
					$string .= "null";
				}elseif(is_string($param)) {
					if($print) {
						Debug::print("{$f} argument \"{$param}\" is a string");
					}
					$string .= single_quote($param);
				}elseif(is_numeric($param)) {
					if($print) {
						Debug::print("{$f} argument \"{$param}\" is a number");
					}
					$string .= $param;
				}else{
					$gottype = gettype($param);
					Debug::error("{$f} none of the above; paramater is a {$gottype}");
				}
			}
			if($this->hasEscapeType() && $this->getEscapeType() == ESCAPE_TYPE_PARENTHESIS) {
				$string = "({$string})";
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getFlatWhereConditionArray(): ?array{
		$f = __METHOD__;
		$print = false;
		$arr = [];
		foreach($this->getParameters() as $param) {
			if(!$param instanceof WhereConditionalInterface) {
				$class = is_object($param) ? $param->getClass() : gettype($param);
				if($print) {
					Debug::print("{$f} argument of class \"{$class}\" is not a WhereConditionalInterface");
				}
				continue;
			}
			$flat = $param->getFlatWhereConditionArray();
			if(!empty($flat)) {
				array_push($arr, ...$flat);
			}
		}
		return $arr;
	}

	public function getSuperflatWhereConditionArray(): ?array{
		$f = __METHOD__;
		$arr = [];
		foreach($this->getParameters() as $param) {
			if($param instanceof BinaryExpressionCommand) {
				Debug::print("{$f} parameter is a binary expression command");
				$rhs = $param->getRightHandSide();
				if($rhs instanceof SelectStatement) {
					Debug::print("{$f} right hand side is a select statement");
					array_push($arr, ...$rhs->getSuperflatWhereConditionArray());
					continue;
				}
				$decl = $rhs->getDeclarationLine();
				Debug::print("{$f} right hand side is not a select statement; it was instantiated {$decl}");
				continue;
			}elseif(!$param instanceof WhereConditionalInterface) {
				$class = is_object($param) ? $param->getClass() : gettype($param);
				Debug::error("{$f} argument of class \"{$class}\" is not a WhereConditionalInterface");
			}
			$flat = $param->getSuperflatWhereConditionArray();
			if(!empty($flat)) {
				array_push($arr, ...$flat);
			}
		}
		return $arr;
	}

	public function getConditionalColumnNames(): array{
		$arr = [];
		foreach($this->getFlatWhereConditionArray() as $where) {
			if($where->inferParameterCount() === 1) {
				array_push($arr, $where->getColumnName());
			}
		}
		return $arr;
	}
}
