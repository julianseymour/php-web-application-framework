<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AssignmentExpression;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

trait ColumnExpressionsTrait{

	use ArrayPropertyTrait;

	public function setColumnExpressions($expressions){
		$f = __METHOD__;
		$temp = [];
		foreach($expressions as $a){
			if($a instanceof AssignmentExpression){
				$temp[]= $a;
			}elseif(is_string($a)){
				$temp[]= new AssignmentExpression($a);
			}else{
				Debug::errpr("{$f} neither of the above");
			}
		}
		$expressions = $temp;
		$temp = null;
		return $this->setArrayProperty("columnExpressions", $expressions);
	}

	public function getColumnExpression($name){
		return $this->getColumnExpressionListMember("columnExpressions", $name);
	}

	public function getColumnExpressionCount(){
		return $this->getArrayPropertyCount("columnExpressions");
	}

	public function getColumnExpressions(){
		return $this->getProperty("columnExpressions");
	}

	public function hasColumnExpressions():bool{
		return $this->hasArrayProperty("columnExpressions");
	}

	public function hasColumnExpression($name):bool{
		return $this->hasArrayPropertyKey("columnExpressions", $name);
	}

	public function mergeColumnExpressions($expressions){
		return $this->mergeArrayProperty("columnExpressions", $expressions);
	}

	public function setColumnExpression($name, $expression){
		return $this->setArrayPropertyValue("columnExpressions", $name, $expression);
	}

	public function withColumnExpressions($expressions){
		$this->setColumnExpressions($expressions);
		return $this;
	}

	protected function getAssignmentListString($expressions, $alias = null){
		$f = __METHOD__;
		$print = false;
		if(!is_array($expressions)){
			Debug::error("{$f} first parameter must be an array");
		}elseif(empty($expressions)){
			Debug::print("{$f} expressions array is empty");
			return null;
		}
		$string = "";
		$i = 0;
		foreach($expressions as $expr){
			if($i++ > 0){
				$string .= ",";
			}
			if($expr instanceof SQLInterface){
				if($print){
					Debug::print("{$f} expression is an SQL interface");
				}
				if($alias !== null && is_string($alias) && !empty($alias)){
					$expr->setAlias($alias);
				}
				$expr = $expr->toSQL();
			}elseif(is_string($expr)){
				if($expr === "?"){
					Debug::error("{$f} passed a question mark");
				}elseif($print){
					Debug::print("{$f} expression is the string \"{$expr}\"");
				}
				$expr = back_quote($expr)."=?";
			}else{
				Debug::error("{$f} expression \"{$expr}\" is neither string nor SQL interface");
			}
			if($print){
				Debug::print("{$f} appending expression \"{$expr}\"");
			}
			$string .= $expr;
		}
		if(empty($string)){
			Debug::error("{$f} empty string");
		}elseif($print){
			Debug::print("{$f} returning \"{$string}\"");
		}
		return $string;
	}
}
