<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

abstract class StoredProcedure extends StoredRoutine{
	
	public static function getRoutineTypeStatic():string{
		return ROUTINE_TYPE_PROCEDURE;
	}
}
