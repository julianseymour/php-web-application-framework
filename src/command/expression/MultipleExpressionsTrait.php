<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultipleExpressionsTrait{

	use ArrayPropertyTrait;

	public function withExpressions($values):object{
		$this->setExpressions($values);
		return $this;
	}
	
	public function setExpressions($values){
		return $this->setArrayProperty('expressions', $values);
	}

	public function pushExpressions(...$values){
		return $this->pushArrayProperty('expressions', ...$values);
	}

	public function mergeExpressions($values){
		return $this->mergeArrayProperty('expressions', $values);
	}

	public function hasExpressions():bool{
		return $this->hasArrayProperty("expressions");
	}

	public function getExpressions(){
		return $this->getProperty("expressions");
	}

	public function getExpressionCount():int{
		return $this->getArrayPropertyCount("expressions");
	}

	public function getExpression($i){
		return $this->getArrayPropertyValue("expressions", $i);
	}
}
