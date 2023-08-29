<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

abstract class StoredFunction extends StoredRoutine{
	
	public abstract static function getReturnTypeStatic():string;
	
	public function __construct(){
		parent::__construct();
		$this->setReturnType(static::getReturnTypeStatic());
	}
	
	public static function getRoutineTypeStatic():string{
		return ROUTINE_TYPE_FUNCTION;
	}
}
