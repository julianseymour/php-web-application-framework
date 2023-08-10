<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalInterface;

class AndCommand extends VariadicExpressionCommand implements WhereConditionalInterface
{

	public function getOperator()
	{
		if (! $this->hasOperator()) {
			return OPERATOR_AND_BOOLEAN;
		}
		return parent::getOperator();
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //AndCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		$parameters = $this->getParameters();
		$num = 1;
		foreach ($parameters as $arg) {
			while ($arg instanceof ValueReturningCommandInterface) {
				$arg = $arg->evaluate();
			}
			if (! $arg) {
				if ($print) {
					Debug::warning("{$f} argument {$num} evaluated to false");
				}
				return false;
			}
			$num ++;
		}
		if ($print) {
			Debug::print("{$f} returning true");
		}
		return true;
	}

	public static function getCommandId(): string
	{
		return "and";
	}

	public function toSQL(): string
	{
		$this->setOperator(OPERATOR_AND_DATABASE);
		return parent::toSQL();
	}

	/*
	 * public function mySQLFormat(){
	 * $this->setOperator(OPERATOR_AND_DATABASE);
	 * return parent::mySQLFormat();
	 * }
	 */
	public function audit(): int
	{
		foreach ($this->getParameters() as $param) {
			$status = $param->audit();
			if ($status !== SUCCESS) {
				return $this->setObjectStatus($status);
			}
		}
		return $status;
	}
}
