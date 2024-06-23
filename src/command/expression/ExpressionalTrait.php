<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ExpressionalTrait{

	protected $expression;

	public function setExpression($expr){
		$f = __METHOD__;
		if($this->hasExpression()){
			$this->release($this->expression);
		}
		return $this->expression = $this->claim($expr);
	}

	public function hasExpression():bool{
		return isset($this->expression);
	}

	public function getExpression(){
		$f = __METHOD__;
		if(!$this->hasExpression()){
			Debug::error("{$f} expression is undefined for this ".$this->getDebugString());
		}
		return $this->expression;
	}

	public function withExpression($expression){
		$this->setExpression($expression);
		return $this;
	}
}
