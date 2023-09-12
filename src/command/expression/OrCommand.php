<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalInterface;

class OrCommand extends VariadicExpressionCommand implements WhereConditionalInterface{

	public function getOperator(){
		if(!$this->hasOperator()) {
			return OPERATOR_OR_BOOLEAN;
		}
		return parent::getOperator();
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$print = false;
		$args = $this->getParameters();
		$num = 1;
		foreach($args as $arg) {
			while ($arg instanceof ValueReturningCommandInterface) {
				$arg = $arg->evaluate();
			}
			if($arg) {
				if($print) {
					Debug::print("{$f} argument {$num} evaluated to true");
				}
				return true;
			}
			$num ++;
		}
		if($print) {
			Debug::print("{$f} returning false");
		}
		return false;
	}

	public static function getCommandId(): string{
		return "or";
	}

	public function toSQL(): string{
		$this->setOperator(OPERATOR_OR_DATABASE);
		return parent::toSQL();
	}
}
