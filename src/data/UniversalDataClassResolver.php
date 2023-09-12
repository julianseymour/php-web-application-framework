<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;


use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class UniversalDataClassResolver extends IntersectionTableResolver{

	public static function getIntersections(){
		$f = __METHOD__;
		$print = false;
		$ret = mods()->getTypeSortedDataStructureClasses();
		if($print) {
			Debug::print("{$f} returning the following:");
			Debug::printArray($ret);
		}
		return $ret;
	}
}
