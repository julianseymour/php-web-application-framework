<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

use JulianSeymour\PHPWebApplicationFramework\command\Routine;

abstract class StoredRoutine extends Routine{
	
	public abstract static function getNameStatic():string;
	
	public abstract static function getRoutineTypeStatic():string;
	
	public abstract static function getDatabaseNameStatic():string;
	
	public function __construct(){
		parent::__construct(
			static::getNameStatic(),
			...$this->getParameters()
		);
		$this->setRoutineType(static::getRoutineTypeStatic());
	}
	
	public function getParameters():?array{
		return [];
	}
}
