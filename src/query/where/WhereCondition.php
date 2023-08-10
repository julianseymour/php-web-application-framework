<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\where;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ParameterCountingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;
use Exception;

class WhereCondition extends ExpressionCommand implements SelectStatementInterface, StringifiableInterface, TypeSpecificInterface, WhereConditionalInterface
{

	use ColumnNameTrait;
	use ParameterCountingTrait;
	use SelectStatementTrait;
	use TypeSpecificTrait;

	public function __construct($varname, string $operator = OPERATOR_EQUALS, ?string $typeSpecifier = null, ?SelectStatement $selectStatement = null)
	{
		parent::__construct();
		$this->setColumnName($varname);
		$this->setOperator($operator);
		if ($typeSpecifier !== null) {
			$this->setTypeSpecifier($typeSpecifier);
		}
		if ($selectStatement !== null) {
			$this->setSelectStatement($selectStatement);
		}
	}

	public function hasUnbindableOperator()
	{
		return false !== array_search($this->getOperator(), [
			OPERATOR_IS_NULL,
			OPERATOR_IS_NOT_NULL
		]);
	}

	public function getTypeSpecifier()
	{
		$f = __METHOD__; //WhereCondition::getShortClass()."(".static::getShortClass().")->getTypeSpecifier()";
		if (! $this->hasTypeSpecifier()) {
			$cn = $this->getColumnName();
			if ($cn instanceof TypeSpecificInterface) {
				return $cn->getTypeSpecifier();
			}
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} no type specifier for column \"{$cn}\". Instantiated {$decl}");
		}
		return $this->typeSpecifier;
	}

	public static function in($varname, ?int $parameterCount = null)
	{
		if ($parameterCount === 1) {
			return new WhereCondition($varname, OPERATOR_EQUALS);
		}
		$where = new WhereCondition($varname, OPERATOR_IN);
		if ($parameterCount !== null) {
			$where->setParameterCount($parameterCount);
		}
		return $where;
	}

	public function getFlatWhereConditionArray(): ?array
	{
		if ($this->hasSelectStatement()) {
			return $this->getSelectStatement()->getFlatWhereConditionArray();
		}
		return [
			$this
		];
	}

	public function getSuperflatWhereConditionArray(): ?array
	{
		if ($this->hasSelectStatement()) {
			return $this->getSelectStatement()->getSuperflatWhereConditionArray();
		}
		return [
			$this
		];
	}

	public function inferParameterCount(): int
	{
		if ($this->hasParameterCount()) {
			return $this->getParameterCount();
		}
		$operator = $this->getOperator();
		switch ($operator) {
			case OPERATOR_IS_NULL:
			case OPERATOR_IS_NOT_NULL:
				return 0;
			case OPERATOR_IN:
			case OPERATOR_NOT_IN:
				return $this->getSelectStatement()->inferParameterCount();
			default:
				return 1;
		}
	}

	public function getParameterCount(): int
	{
		if ($this->hasParameterCount()) {
			return $this->parameterCount;
		}
		return $this->inferParameterCount();
	}

	public function getRequiredParameterCount(): int
	{
		return $this->inferParameterCount();
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //WhereCondition::getShortClass()."(".static::getShortClass().")->toSQL()";
		try {
			$print = false;
			$select = null;
			if ($this->hasSelectStatement()) {
				$select = $this->getSelectStatement();
				if ($select instanceof SQLInterface) {
					$select = $select->toSQL();
				}
			}

			$operator = $this->getOperator();
			switch ($operator) {
				case OPERATOR_IN:
				case OPERATOR_NOT_IN:
					$qmark = "(";
					if ($select !== null) {
						if ($print) {
							Debug::print("{$f} select statement is not null");
						}
						$qmark .= $select;
					} else {
						if ($print) {
							Debug::print("{$f} select statement is null");
						}
						for ($i = 0; $i < $this->getParameterCount(); $i ++) {
							if ($i > 0) {
								$qmark .= ",";
							}
							$qmark .= "?";
						}
					}
					$qmark .= ")";
					break;
				case OPERATOR_IS_NULL:
				case OPERATOR_IS_NOT_NULL:
					$qmark = ""; // null";
					break;
				case OPERATOR_STARTS_WITH:
					$operator = "like";
					if ($select !== null) {
						$qmark = $select;
					} else {
						$qmark = "?";
					}
					$qmark = "CONCAT('%',{$qmark})";
					break;
				case OPERATOR_CONTAINS:
					$operator = "like";
					if ($select !== null) {
						$qmark = $select;
					} else {
						$qmark = "?";
					}
					$qmark = "CONCAT('%',{$qmark},'%')";
					break;
				case OPERATOR_ENDS_WITH:
					$operator = "like";
					if ($select !== null) {
						$qmark = $select;
					} else {
						$qmark = "?";
					}
					$qmark = "CONCAT({$qmark},'%')";
					break;
				default:
					if ($select !== null) {
						$qmark = $select;
					} else {
						$qmark = "?";
					}
					break;
			}
			$var = $this->getColumnName();
			if ($var instanceof SQLInterface) {
				$var = $var->toSQL();
			}
			$string = "{$var} {$operator} {$qmark}";
			if ($this->hasEscapeType() && $this->getEscapeType() === ESCAPE_TYPE_PARENTHESIS) {
				$string = "({$string})";
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //WhereCondition::getShortClass()."(".static::getShortClass().")->evaluate()";
		Debug::vestigal($f);
	}

	public static function getCommandId(): string
	{
		return "where";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->parameterCount);
		unset($this->selectStatement);
		unset($this->typeSpecifier);
	}

	public function audit(): int
	{
		return SUCCESS;
	}

	/*
	 * public function mySQLFormat(){
	 * return SUCCESS;
	 * }
	 */
	public function getConditionalColumnNames(): array
	{
		if ($this->inferParameterCount() === 0) {
			return [];
		}
		return [
			$this->getColumnName()
		];
	}

	public function __toString(): string
	{
		return $this->toSQL();
	}
}