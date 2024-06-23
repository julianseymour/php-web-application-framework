<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class WithClause extends Basic implements StaticPropertyTypeInterface, SQLInterface{

	use ArrayPropertyTrait;
	use StaticPropertyTypeTrait;

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"commonTableExpressions" => CommonTableExpression::class
		];
	}

	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}
	
	public function setCommonTableExpressions($ctes){
		return $this->setArrayProperty("commonTableExpressions", $ctes);
	}

	public function hasCommonTableExpressions():bool{
		return $this->hasArrayProperty("commonTableExpressions");
	}

	public function getCommonTableExpressions(){
		return $this->getProperty("commonTableExpressions");
	}

	public function pushCommonTableExpressions(...$ctes):int{
		return $this->pushArrayProperty("commonTableExpressions", ...$ctes);
	}

	public function mergeCommonTableExpressions($ctes){
		return $this->mergeArrayProperty("commonTableExpressions", $ctes);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"recursive"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"recursive"
		]);
	}
	
	public function getCommonTableExpression($num): CommonTableExpression{
		return $this->getArrayPropertyValue("commonTableExpressions", $num);
	}

	public function setRecursiveFlag(bool $value = true):bool{
		$f = __METHOD__;
		if($value && $this->hasCommonTableExpressions() && $this->getCommonTableExpressionCount() > 1){
			Debug::error("{$f} unsupported: syntax for ");
		}
		return $this->setFlag("recursive", $value);
	}

	public function getRecursiveFlag():bool{
		return $this->getFlag("recursive");
	}

	public static function recursive($cteName, $subquery):WithClause{
		$with = new WithClause();
		$with->setRecursiveFlag(true);
		$with->pushCommonTableExpressions(new CommonTableExpression($cteName, $subquery));
		return $with;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			/*
			 * WITH [RECURSIVE] cte_name [(col_name [, col_name] ...)] AS (subquery) [, cte_name [(col_name [, col_name] ...)] AS (subquery)] ...
			 */
			$string .= "with ";
			if($this->getRecursiveFlag()){
				$string .= "recursive ";
			}
			$ctes = [];
			foreach($this->getCommonTableExpressions() as $c){
				if($c instanceof SQLInterface){
					$c = $c->toSQL();
				}
				array_push($ctes, $c);
			}
			$string .= implode(',', $ctes);
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
