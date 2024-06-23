<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;

class AssignmentExpression extends BinaryExpressionCommand implements SQLInterface{
	
	use AliasTrait;
	
	public function __construct(?string $column_name=null, $rhs=null){
		parent::__construct($column_name, OPERATOR_EQUALS, $rhs);
	}
	
	public function hasOperator():bool{
		return true;
	}
	
	public function setOperator($operator){
		return $operator;
	}
	
	public function getOperator(){
		return OPERATOR_EQUALS;
	}
	
	public function toSQL():string{
		$ret = $this->getLeftHandSide()."=";
		if($this->hasAlias()){
			$alias = $this->getAlias();
			if($alias instanceof SQLInterface){
				$alias = $alias->toSQL();
			}
			$ret .= "{$alias}.";
		}
		if($this->hasRightHandSide()){
			$rhs = $this->getRightHandSide();
			if(is_string($rhs)){
				$rhs = back_quote($rhs);
			}elseif($rhs instanceof SQLInterface){
				$rhs = $rhs->toSQL();
			}
		}else{
			$rhs = "?";
		}
		$ret .= $rhs;
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->alias, $deallocate);
	}
}
