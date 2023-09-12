<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class ExpressionCommand extends Command implements ValueReturningCommandInterface
{

	protected $operator;

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			"negated"
		]);
	}

	public function negate()
	{
		$this->setFlag("negated", true);
		return $this;
	}

	public function isNegated()
	{
		return $this->getFlag("negated");
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->operator);
	}

	public function setOperator($operator)
	{
		if($operator == null) {
			unset($this->operator);
			return null;
		}
		return $this->operator = $operator;
	}

	public function hasOperator()
	{
		return isset($this->operator);
	}

	public function getOperator()
	{
		$f = __METHOD__; //ExpressionCommand::getShortClass()."(".static::getShortClass().")->getOperator()";
		if(!$this->hasOperator()) {
			Debug::error("{$f} operator is undefined");
		}elseif($this->isNegated()) {
			return static::negateOperator($this->operator);
		}
		return $this->operator; // hasOperator() ? $this->operator : OPERATOR_EQUALSEQUALS;
	}
}
