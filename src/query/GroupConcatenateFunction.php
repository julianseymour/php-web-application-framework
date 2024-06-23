<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\command\expression\MultipleExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\common\DelimiterTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;

class GroupConcatenateFunction extends Basic implements SQLInterface{
	
	use DelimiterTrait;
	use DistinctionTrait;
	use MultipleExpressionsTrait;
	use OrderableTrait;
	
	public function toSQL():string{
		//GROUP_CONCAT(
		$string = "GROUP_CONCAT(";
			//[DISTINCT]
			if($this->hasDistinction()){
				$string .= "DISTINCT ";
			}
			//expr [,expr ...]
			$count = 0;
			foreach($this->getExpressions() as $expr){
				if($count++ > 0){
					$string .= ',';
				}
				if($expr instanceof SQLInterface){
					$expr = $expr->toSQL();
				}
				$string .= $expr;
			}
			//[ORDER BY {unsigned_integer | col_name | expr} [ASC | DESC] [,col_name ...]]
			if($this->hasOrderBy()){
				foreach($this->getOrderBy() as $order){
					if($order instanceof SQLInterface){
						$order = $order->toSQL();
					}
					$string .= " {$order}";
				}
			}
			//[SEPARATOR str_val]
			if($this->hasDelimiter()){
				$string .= " SEPARATOR ".single_quote($this->getDelimiter());
			}
		//)
		$string .= ")";
		return $string;
	}
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasDelimiter()){
			$this->release($this->delimiter, $deallocate);
		}
		if($this->hasDistinction()){
			$this->release($this->distinction, $deallocate);
		}
		if($this->hasOrderBy()){
			$this->release($this->orderByExpression, $deallocate);
		}
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}
}
