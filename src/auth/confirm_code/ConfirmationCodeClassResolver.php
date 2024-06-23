<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;
use function JulianSeymour\PHPWebApplicationFramework\mods;

class ConfirmationCodeClassResolver extends IntersectionTableResolver{
	
	public static function getIntersections():array{
		$ret = [
			DATATYPE_CONFIRMATION_CODE => []
		];
		foreach(mods()->getDataStructureClasses() as $class){
			if(is_a($class, ConfirmationCode::class, true)){
				$ret[DATATYPE_CONFIRMATION_CODE][$class::getConfirmationCodeTypeStatic()] = $class;
			}
		}
		return $ret;
	}
	
	public static function getSubtypability():string{
		return SUBTYPABILITY_ALL;
	}
}
