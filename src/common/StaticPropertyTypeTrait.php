<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\replicate;

trait StaticPropertyTypeTrait{

	//use PropertiesTrait;

	public abstract static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array;

	public static function getPropertyTypeStatic($key, ?StaticPropertyTypeInterface $object = null){
		$types = static::declarePropertyTypes($object);
		if(array_key_exists($key, $types)){
			$ret = $types[$key];
			foreach(array_keys($types) as $k){
				if($k === $key){
					continue;
				}
				deallocate($types[$k]);
			}
			unset($types);
			return $ret;
		}
		deallocate($types);
		return null;
	}
}
