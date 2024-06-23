<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

abstract class ExtremeValueCommand extends VariadicExpressionCommand{
	
	public static abstract function compare($param, $value):bool;
	
	public function evaluate(?array $params = null){
		if(empty($params)){
			return null;
		}
		$ext = $params[0];
		while($ext instanceof ValueReturningCommandInterface){
			$ext = $ext->evaluate();
		}
		if(count($params) === 1){
			return $ext;
		}
		for($i = 1; $i < count($params); $i++){
			$param = $params[$i];
			while($param instanceof ValueReturningCommandInterface){
				$param = $param->evaluate();
			}
			if($this->compare($param, $ext)){
				$ext = $param;
			}
		}
		return $ext;
	}
	
	public function toSQL():string{
		return $this->getCommandId()."(".$this->getParameter(0).")";
	}
}
