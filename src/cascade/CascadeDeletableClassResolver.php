<?php

namespace JulianSeymour\PHPWebApplicationFramework\cascade;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;

class CascadeDeletableClassResolver extends IntersectionTableResolver{
	
	public static function getIntersections(){
		$ret = [];
		foreach(mods()->getDataStructureClasses() as $dsc){
			if(is_a($dsc, CascadeDeletableInterface::class, true)){
				$type = $dsc::getDataType();
				if(is_a($dsc, StaticSubtypeInterface::class, true)){
					if(!array_key_exists($type, $ret)){
						$ret[$type] = [];
					}
					$ret[$type][$dsc::getSubtypeStatic()] = $dsc;
				}else{
					$ret[$type] = $dsc;
				}
			}
		}
		return $ret;
	}
}
