<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ComputerLanguageTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class BinaryExpressionCommand extends ExpressionCommand implements JavaScriptInterface, SQLInterface, WhereConditionalInterface
{

	use ComputerLanguageTrait;

	protected $leftHandSide;

	protected $rightHandSide;

	public function __construct($lhs = null, $operator = null, $rhs = null)
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		$print = false;
		parent::__construct();
		if (! empty($lhs)) {
			$this->setLeftHandSide($lhs);
		}
		if (! empty($operator)) {
			$this->setOperator($operator);
		}
		if ($rhs !== null) {
			$this->setRightHandSide($rhs);
		}

		if ($print && $this->hasLeftHandSide() && $this->hasOperator() && $this->hasRightHandSide()) {
			if ($operator === OPERATOR_EQUALS) {
				$lhs = $this->getLeftHandSide();
				$rhs = $this->getRightHandSide();
				if (is_string($lhs) && is_string($rhs) && $lhs !== $rhs) {
					Debug::error("{$f} false string comparison");
				}
			}
		}
	}

	public static function negateOperator($op)
	{
		$f = __METHOD__; //ExpressionCommand::getShortClass()."(".static::getShortClass().")::negateOperator()";
		try {
			switch ($op) {
				case OPERATOR_EQUALS:
				case OPERATOR_EQUALSEQUALS:
					return OPERATOR_NOTEQUALS;
				case OPERATOR_GREATERTHAN:
					return OPERATOR_LESSTHANEQUALS;
				case OPERATOR_GREATERTHANEQUALS:
					return OPERATOR_LESSTHAN;
				case OPERATOR_LESSTHAN:
					return OPERATOR_GREATERTHANEQUALS;
				case OPERATOR_LESSTHANEQUALS:
					return OPERATOR_GREATERTHAN;
				case OPERATOR_LESSTHANGREATERTHAN:
				case OPERATOR_NOTEQUALS:
					return OPERATOR_EQUALSEQUALS;
				/*
				 * case OPERATOR_MINUS:
				 * return OPERATOR_PLUS;
				 * case OPERATOR_PLUS:
				 * return OPERATOR_MINUS;
				 * case OPERATOR_DIVISION:
				 * case OPERATOR_MULT:
				 * return $op;
				 */
				default:
					Debug::error("{$f} unnegatable operator \"{$op}\"");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function equals($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_EQUALSEQUALS, $rhs);
	}

	public static function assign($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_EQUALS, $rhs);
	}

	public static function lessThan($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_LESSTHAN, $rhs);
	}

	public static function lessThanOrEquals($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_LESSTHANEQUALS, $rhs);
	}

	public static function greaterThan($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_GREATERTHAN, $rhs);
	}

	public static function greaterThanOrEquals($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_GREATERTHAN, $rhs);
	}

	public static function notEquals($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_NOTEQUALS, $rhs);
	}

	public static function add($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_PLUS, $rhs);
	}

	public static function subtract($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_MINUS, $rhs);
	}

	public static function multiply($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_MULT, $rhs);
	}

	public static function divide($lhs, $rhs): BinaryExpressionCommand
	{
		return new BinaryExpressionCommand($lhs, OPERATOR_DIVISION, $rhs);
	}

	public function hasLeftHandSide()
	{
		return isset($this->leftHandSide);
	}

	public function setLeftHandSide($lhs)
	{
		if (! isset($lhs)) {
			unset($this->leftHandSide);
			return null;
		}
		return $this->leftHandSide = $lhs;
	}

	public function getLeftHandSide()
	{
		/*
		 * $f = __METHOD__; //IfCommand::getShortClass()."(".static::getShortClass().")->getLeftHandSide()";
		 * if(!$this->hasLeftHandSide()){
		 * Debug::error("{$f} left hand side is undefined");
		 * }
		 */
		return $this->leftHandSide;
	}

	public function hasRightHandSide()
	{
		return $this->rightHandSide !== null;
	}

	public function getRightHandSide()
	{
		$f = __METHOD__; //IfCommand::getShortClass()."(".static::getShortClass().")->getRightHandSide()";
		/*
		 * if(!$this->hasRightHandSide()){
		 * Debug::error("{$f} right hand side is undefined");
		 * }
		 */
		return $this->rightHandSide;
	}

	public function setRightHandSide($rhs)
	{
		if ($rhs === null) {
			unset($this->rightHandSide);
			return null;
		}
		return $this->rightHandSide = $rhs;
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		try {
			Json::echoKeyValuePair("lhs", $this->getLeftHandSide());
			if (! $this->hasRightHandSide() && ! $this->hasOperator()) {
				if ($this->isNegated()) {
					Debug::error("{$f} haven't dealt with negations client side");
					Json::echoKeyValuePair("negated", "true", $destroy);
				}
			} else {
				Json::echoKeyValuePair("operator", $this->getOperator());
				Json::echoKeyValuePair("rhs", $this->getRightHandSide());
			}
			parent::echoInnerJson($destroy);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->leftHandSide);
		unset($this->rightHandSide);
	}

	public static function getCommandId(): string
	{
		return "binaryExpression";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		try {
			$lhs = $this->getLeftHandSide();
			if ($lhs instanceof Command) {
				$lhs = $lhs->evaluate();
			}
			if (! $this->hasRightHandSide() && ! $this->hasOperator()) {
				return $lhs;
			}
			$rhs = $this->getRightHandSide();
			if ($rhs instanceof Command) {
				$rhs = $rhs->evaluate();
			}
			$operator = $this->getOperator();
			switch ($operator) {
				case OPERATOR_AND_BITWISE:
					return $lhs & $rhs;
				case OPERATOR_AND_BOOLEAN:
					return $lhs && $rhs;
				case OPERATOR_OR_BITWISE:
					return $lhs | $rhs;
				case OPERATOR_OR_BOOLEAN:
					return $lhs || $rhs;
				case OPERATOR_XOR:
					return $lhs ^ $rhs;
				case OPERATOR_IDENTITY:
					return $lhs === $rhs;
				case OPERATOR_LESSTHAN:
					return $lhs < $rhs;
				case OPERATOR_LESSTHANEQUALS:
					return $lhs <= $rhs;
				case OPERATOR_EQUALSEQUALS:
				case OPERATOR_EQUALS:
					return $lhs == $rhs;
				case OPERATOR_GREATERTHANEQUALS:
					return $lhs >= $rhs;
				case OPERATOR_GREATERTHAN:
					return $lhs > $rhs;
				case OPERATOR_NOTEQUALSEQUALS:
					return $lhs !== $rhs;
				case OPERATOR_NOTEQUALS:
				case OPERATOR_LESSTHANGREATERTHAN:
					return $lhs != $rhs;
				case OPERATOR_PLUS:
					return $lhs + $rhs;
				case OPERATOR_MINUS:
					return $lhs - $rhs;
				case OPERATOR_MULT:
					if (! is_numeric($lhs)) {
						Debug::error("{$f} left hand side \"{$lhs}\" is not a number");
					} elseif (! is_numeric($rhs)) {
						Debug::error("{$f} right hand side \"{$rhs}\" is not a number");
					}
					return $lhs * $rhs;
				case OPERATOR_DIVISION:
					if (! is_numeric($lhs)) {
						Debug::error("{$f} left hand side \"{$lhs}\" is not a number");
					} elseif (! is_numeric($rhs)) {
						Debug::error("{$f} right hand side \"{$rhs}\" is not a number");
					}
					return $lhs / $rhs;
				case OPERATOR_MODULO:
					return $lhs % $rhs;
				default:
					return Debug::error("{$f} Invalid operator \"{$operator}\"");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function toSQL(): string
	{
		$this->setComputerLanguage(COMPUTER_LANGUAGE_SQL);
		return $this->__toString();
	}

	public function toJavaScript(): string
	{
		$this->setComputerLanguage(COMPUTER_LANGUAGE_JAVASCRIPT);
		return $this->__toString();
	}

	public function __toString(): string
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->__toString()";
		try {
			$print = false;
			$lhs = $this->getLeftHandSide();
			if (! $this->hasComputerLanguage()) {
				Debug::error("{$f} computer language is undefined");
			}
			$cl = $this->getComputerLanguage();
			if ($print) {
				Debug::print("{$f} computer language is \"{$cl}\"");
			}
			if ($lhs instanceof JavaScriptInterface && $cl === COMPUTER_LANGUAGE_JAVASCRIPT) {
				$lhs = $lhs->toJavaScript();
			} elseif ($lhs instanceof SQLInterface && $cl === COMPUTER_LANGUAGE_SQL) {
				$lhs = $lhs->toSQL();
			} elseif (is_string($lhs) || $lhs instanceof StringifiableInterface) {
				$lhs = single_quote($lhs);
			}
			$operator = $this->getOperator();
			if ($operator instanceof JavaScriptInterface && $cl === COMPUTER_LANGUAGE_JAVASCRIPT) {
				$operator = $operator->toJavaScript();
			} elseif ($operator instanceof SQLInterface && $cl === COMPUTER_LANGUAGE_SQL) {
				$operator = $operator->toSQL();
			}
			if ($operator === OPERATOR_EQUALSEQUALS && $cl === COMPUTER_LANGUAGE_SQL) {
				$operator = OPERATOR_EQUALS;
			}
			$rhs = $this->getRightHandSide();
			if ($rhs instanceof JavaScriptInterface && $cl === COMPUTER_LANGUAGE_JAVASCRIPT) {
				$rhs = $rhs->toJavaScript();
			} elseif ($rhs instanceof SQLInterface && $cl === COMPUTER_LANGUAGE_SQL) {
				$rhs = $rhs->toSQL();
			} elseif (is_string($rhs) || $rhs instanceof StringifiableInterface) {
				if ($print) {
					Debug::print("{$f} right hand side is a string");
				}
				$rhs = single_quote($rhs);
			} elseif ($print) {
				$gottype = is_object($rhs) ? $rhs->getClass() : gettype($rhs);
				Debug::print("{$f} right hand side type is \"{$gottype}\"");
			}
			$ret = "{$lhs} {$operator} {$rhs}";
			if ($this->hasEscapeType() && $this->getEscapeType() === ESCAPE_TYPE_PARENTHESIS) {
				$ret = "({$ret})";
			}

			if ($print) {
				Debug::print("{$f} returning \"{$ret}\"");
			}

			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasWhereConditionalRightHandSide(): bool
	{
		if (! $this->hasRightHandSide()) {
			return false;
		}
		return $this->getRightHandSide() instanceof WhereConditionalInterface;
	}

	public function getSuperflatWhereConditionArray(): ?array
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->getSuperflatWhereConditionArray()";
		if (! $this->hasWhereConditionalRightHandSide()) {
			$decl = $this->getDeclarationLine();
			Debug::warning("{$f} right hand side is undefined; declared {$decl}");
			return null;
		}
		return $this->getRightHandSide()->getSuperflatWhereConditionArray();
	}

	public function getConditionalColumnNames(): array
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->getConditionalColumnNames()";
		if (! $this->hasWhereConditionalRightHandSide()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} right hand side is undefined; declared {$decl}");
		}
		return $this->getRightHandSide()->getConditionalColumnNames();
	}

	public function getFlatWhereConditionArray(): ?array
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->getFlatWhereConditionArray()";
		$print = false;
		if (! $this->hasWhereConditionalRightHandSide()) {
			if ($print) {
				$decl = $this->getDeclarationLine();
				Debug::warning("{$f} right hand side is undefined; declared {$decl}");
			}
			return null;
		}
		return $this->getRightHandSide()->getFlatWhereConditionArray();
	}

	public function audit(): int
	{
		$f = __METHOD__; //BinaryExpressionCommand::getShortClass()."(".static::getShortClass().")->audit()";
		if (! $this->hasWhereConditionalRightHandSide()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} right hand side is undefined; declared {$decl}");
		}
		return $this->getRightHandSide()->audit();
	}
}
