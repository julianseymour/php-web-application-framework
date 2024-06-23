<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait OrderableTrait{

	protected $orderByExpression;

	public function hasOrderBy(): bool{
		return !empty($this->orderByExpression);
	}

	public function getOrderBy(): array{
		$f = __METHOD__;
		if(!$this->hasOrderBy()){
			Debug::error("{$f} order by is undefined");
		}
		return $this->orderByExpression;
	}

	public function setOrderBy(...$order_by): array{
		$f = __METHOD__;
		foreach(array_keys($order_by) as $i){
			if(is_string($order_by[$i])){
				$order_by[$i] = new OrderByClause($order_by[$i], DIRECTION_ASCENDING);
			}
		}
		if($this->hasOrderBy()){
			$this->release($this->orderByExpression);
		}
		return $this->orderByExpression = $this->claim($order_by);
		//return $this->getOrderBy();
	}

	public function orderBy(...$obe){
		$this->setOrderBy(...$obe);
		return $this;
	}

	protected function getOrderByString(): string{
		$f = __METHOD__;
		if(!$this->hasOrderBy()){
			Debug::error("{$f} order by is undefined");
		}
		$string = "";
		$count = count($this->orderByExpression);
		for ($i = 0; $i < $count; $i ++){
			$term = $this->orderByExpression[$i];
			if(is_array($term)){
				Debug::error("{$f} somehow, the order by term is an array");
			}elseif($i > 0){
				$string .= ", ";
			}elseif(is_int($term)){
				Debug::error("{$f} term is the integer \"{$term}\"");
			}
			if($term instanceof SQLInterface){
				$term = $term->toSQL();
			}
			$string .= $term;
		}
		return $string;
	}
}