<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

class SumCommand extends ExpressionCommand implements SQLInterface{

	use ExpressionalTrait;
	
	public function __construct($e = null){
		$f = __METHOD__;
		parent::__construct();
		if($e !== null){
			$this->setExpression($e);
		}
	}

	public static function getCommandId(): string{
		return "sum";
	}

	public function evaluate(?array $params = null){
		$sum = 0;
		foreach($this->getParameters() as $p){
			if($p instanceof ValueReturningCommandInterface){
				while($p instanceof ValueReturningCommandInterface){
					$p = $p->evaluate();
				}
			}
			$sum += $p;
		}
		return $sum;
	}

	public function toSQL(): string{
		$e = $this->getExpression();
		if($e instanceof SQLInterface){
			$e = $e->toSQL();
		}
		return "sum({$e})";
	}
	
	public function copy($that):int{
		$f = __METHOD__;
		$ret = parent::copy($that);
		if($that->hasExpression()){
			$expr = $that->getExpression();
			$this->setExpression(replicate($expr));
		}
		return $ret;
	}

}
