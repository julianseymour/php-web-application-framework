<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;

class TranslatableObjectResolver extends IntersectionTableResolver{
	
	public static function getIntersections(){
		$ret = [];
		foreach(mods()->getDataStructureClasses() as $dsc){
			if(is_a($dsc, MultilingualStringInterface::class, true)){
				$type = $dsc::getDataType();
				if(method_exists($dsc, StaticSubtypeInterface::class)){
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
