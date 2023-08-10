<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AliasTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalInterface;

class ColumnAlias extends Command implements SQLInterface, StringifiableInterface, WhereConditionalInterface
{

	use AliasTrait;
	use ExpressionalTrait;

	public function __construct($expr = null, ?string $alias = null)
	{
		parent::__construct();
		if ($expr !== null) {
			$this->setExpression($expr);
		}
		if ($alias !== null) {
			$this->setAlias($alias);
		}
	}

	public function toSQL(): string
	{
		$expr = $this->getExpression();
		if ($expr instanceof SQLInterface) {
			$expr = $expr->toSQL();
		}
		return "({$expr}) as " . back_quote($this->getAlias());
	}

	public static function getCommandId(): string
	{
		return "as";
	}

	public static function create($expr = null, ?string $alias = null): ColumnAlias
	{
		return new ColumnAlias($expr, $alias);
	}

	public function as(?string $alias): ColumnAlias
	{
		$this->setAlias($alias);
		return $this;
	}

	public function getColumnName()
	{
		return $this->getAlias();
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->expression);
		unset($this->alias);
	}

	public function __toString(): string
	{
		return $this->toSQL();
	}

	public function getSuperflatWhereConditionArray(): ?array
	{
		$f = __METHOD__; //ColumnAlias::getShortClass()."(".static::getShortClass().")->getSuperflatWhereConditionArray()";
		if (! $this->hasWhereConditionalExpression()) {
			Debug::error("{$f} expression is undefined");
		}
		return $this->getExpression()->getSuperflatWhereConditionArray();
	}

	public function getConditionalColumnNames(): array
	{
		$f = __METHOD__; //ColumnAlias::getShortClass()."(".static::getShortClass().")->getConditionalColumnNames()";
		if (! $this->hasWhereConditionalExpression()) {
			Debug::error("{$f} expression is undefined");
		}
		return $this->getExpression()->getConditionalColumnNames();
	}

	public function getFlatWhereConditionArray(): ?array
	{
		$f = __METHOD__; //ColumnAlias::getShortClass()."(".static::getShortClass().")->getFlatWhereConditionArray()";
		if (! $this->hasWhereConditionalExpression()) {
			Debug::error("{$f} expression is undefined");
		}
		return $this->getExpression()->getFlatWhereConditionArray();
	}

	public function audit(): int
	{
		$f = __METHOD__; //ColumnAlias::getShortClass()."(".static::getShortClass().")->audit()";
		if (! $this->hasWhereConditionalExpression()) {
			Debug::error("{$f} expression is undefined");
		}
		return $this->getExpression()->audit();
	}

	public function hasWhereConditionalExpression(): bool
	{
		$f = __METHOD__; //ColumnAlias::getShortClass()."(".static::getShortClass().")->hasWhereConditionalExpression()";
		$print = false;
		if (! $this->hasExpression()) {
			if ($print) {
				Debug::print("{$f} expression is undefined");
			}
			return false;
		}
		return $this->getExpression() instanceof WhereConditionalInterface;
	}

	public function getParameters(): ?array
	{
		$f = __METHOD__; //ColumnAlias::getShortClass()."(".static::getShortClass().")->getParameters()";
		if (! $this->hasWhereConditionalExpression()) {
			Debug::error("{$f} this is not a where conditional expression");
		}
		return $this->getExpression()->getParameters();
	}

	public function hasParameters(): bool
	{
		return $this->hasWhereConditionalExpression() && $this->getExpression()->hasParameters();
	}

	public function inferParameterCount(): int
	{
		return $this->hasWhereConditionalExpression() ? $this->getExpression()->inferParameterCount() : 0;
	}

	public function getTypeSpecifier(): string
	{
		$f = __METHOD__; //ColumnAlias::getShortClass()."(".static::getShortClass().")->getTypeSpecifier()";
		if (! $this->hasWhereConditionalExpression()) {
			Debug::error("{$f} expression is not where conditional");
		}
		return $this->getExpression()->getTypeSpecifier();
	}
}
