<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

/*
 * A trait for classes that maintain a reference to a ColumnDefinitionInterface.
 * Not to be confused with ColumnDefinitionTrait, which is used by the column definition object itself
 */
trait SingleColumnDefiningTrait{

	protected $columnDefinition;

	public function setColumnDefinition(Datum $column){
		$f = __METHOD__;
		if(!$column instanceof Datum){
			Debug::error("{$f} this function only accepts datums");
		}elseif($this->hasColumnDefinition()){
			$this->release($this->columnDefinition);
		}
		return $this->columnDefinition = $this->claim($column);
	}

	public function hasColumnDefinition():bool{
		return isset($this->columnDefinition);
	}

	public function getColumnDefinition(): Datum{
		$f = __METHOD__;
		if(!$this->hasColumnDefinition()){
			Debug::error("{$f} column definition is undefined");
		}
		return $this->columnDefinition;
	}
}
