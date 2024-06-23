<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\is_abstract;
use JulianSeymour\PHPWebApplicationFramework\core\ClassResolver;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\common\SubtypeInterface;

abstract class IntersectionTableResolver extends ClassResolver{

	public abstract static function getIntersections();
	
	/**
	 * Returns a value indicating whether none, some (but not all), or all of the resolvable classes are subtypable 
	 * @return string
	 */
	public static function getSubtypability():string{
		$f = __METHOD__;
		if(CACHE_ENABLED){
			$cache_key = static::getShortClass()."Subtypability";
			if(cache()->hasAPCu($cache_key)){
				return cache()->getAPCu($cache_key);
			}
			$cache = true;
		}
		$intersections = static::getIntersections();
		if(empty($intersections)){
			Debug::error("{$f} getIntersections returned null/empty array");
		}
		$no = false;
		$yes = false;
		foreach($intersections as $class){
			if(is_array($class)){
				$yes = true;
			}elseif(
				is_string($class) 
				&& class_exists($class) 
				&& is_a($class, SubtypeInterface::class, true)
			){
				$yes = true;
			}else{
				$no = true;
			}
			if($yes && $no){
				break;
			}
		}
		if($yes){
			if($no){
				$subtypability = SUBTYPABILITY_PARTIAL;
			}else{
				$subtypability = SUBTYPABILITY_ALL;
			}
		}elseif($no){
			$subtypability = SUBTYPABILITY_NONE;
		}else{
			Debug::error("{$f} impossible subtypability for class ".static::getShortClass());
		}
		if($cache){
			cache()->setAPCu($cache_key, $subtypability);
		}
		return $subtypability;
	}
	
	public static function resolveClass(Datum $datum):string{
		$f = __METHOD__;
		$name = $datum->getName();
		$print = false;
		if($print){
			Debug::print("{$f} about to resolve foreign data structure class of column \"{$name}\"");
		}
		if(!$datum instanceof Datum){
			Debug::error("{$f} input parameter must be a datum");
		}
		$type_hint = $datum->getForeignDataType();
		if($datum->hasForeignDataSubtype() || $datum->hasDataStructure() && $datum->hasForeignDataSubtypeName()){
			$subtype = $datum->getForeignDataSubtype();
		}else{
			if($print){
				Debug::print("{$f} no way of getting foreign data subtype");
			}
			$subtype = null;
		}
		if(empty($type_hint) && empty($subtype)){
			$ds = $datum->getDataStructure();
			$key = $ds->hasIdentifierValue() ? $ds->getIdentifierValue() : "[undefined]";
			Debug::error("{$f} data type and subtype hints are both empty for column \"{$name}\" from ".$ds->getDebugString()." with key \"{$key}\"");
		}elseif($subtype === CONST_ERROR){
			Debug::error("{$f} foreign data subtype of column \"{$name}\" is ERROR; primary type is \"{$type_hint}\"");
		}
		if($print){
			Debug::print("{$f} about to get data structure class for datum \"{$name}\" with type \"{$type_hint}\" and subtype \"{$subtype}\"");
		}
		return static::resolveForeignDataStructureClass($type_hint, $subtype);
	}

	public static function resolveForeignDataStructureClass($type_hint, $subtype):string{
		$f = __METHOD__;
		$print = false;
		if(empty($type_hint)){
			Debug::error("{$f} type hint is empty. This resolver's class is ".static::getShortClass());
		}
		$intersections = static::getIntersections();
		if($print){
			Debug::print("{$f} about to resolve foreign data structure class for type \"{$type_hint}\", subtype \"{$subtype}\" from the the following intersections");
			Debug::printArray($intersections);
		}
		if(!is_array($intersections)){
			Debug::error("{$f} intersections list is not an array");
		}elseif(!array_key_exists($type_hint, $intersections)){
			Debug::error("{$f} type \"{$type_hint}\" is invalid");
		}elseif(is_string($intersections[$type_hint])){
			if(!class_exists($intersections[$type_hint])){
				Debug::error("{$f} class \"{$intersections[$type_hint]}\" does not exist");
			}
			return $intersections[$type_hint];
		}elseif(empty($subtype)){
			Debug::warning("{$f} subtype is undefined, needed to resolve class from this sub array:");
			Debug::printArray($intersections[$type_hint]);
			Debug::printStackTrace();
		}elseif(!is_array($intersections[$type_hint])){
			Debug::error("{$f} intersections at index \"{$type_hint}\" is not a class name or array");
		}elseif(!array_key_exists($subtype, $intersections[$type_hint])){
			Debug::error("{$f} subtype \"{$subtype}\" is invalid");
		}
		return $intersections[$type_hint][$subtype];
	}

	private static function generateIntersectionData($type_hint, $subtype, $datum):IntersectionData{
		$f = __METHOD__;
		$print = false;
		$name = $datum->getName();
		$fdsc = static::resolveForeignDataStructureClass($type_hint, $subtype);
		if(!class_exists($fdsc)){
			Debug::error("{$f} foreign class \"{$fdsc}\" does not exist");
		}elseif(!is_a($fdsc, DataStructure::class, true)){
			Debug::error("{$f} foreign class \"{$fdsc}\" does not extend DataStructure for column \"{$name}\"");
		}elseif(is_abstract($fdsc)){
			Debug::error("{$f} foreign class \"{$fdsc}\" is abstract for column \"{$name}\"");
		}
		$hdsc = $datum->getDataStructureClass();
		if(!is_a($fdsc, DataStructure::class, true)){
			Debug::error("{$f} host class \"{$hdsc}\" does not extend DataStructure for column \"{$name}\"");
		}elseif(is_abstract($hdsc)){
			Debug::error("{$f} host class \"{$hdsc}\" is abstract for column \"{$name}\"");
		}elseif($print){
			Debug::print("{$f} about to create intersection data for column \"{$name}\" with host class \"{$hdsc}\" and foreign class \"{$fdsc}\"");
		}
		$intersection = new IntersectionData($hdsc, $fdsc, $name);
		return $intersection;
	}

	public static function getAllPossibleIntersectionClasses():?array{
		$classes = [];
		foreach(static::getIntersections() as $arr){
			if(is_string($arr)){
				array_push($classes, $arr);
			}elseif(is_array($arr)){
				foreach($arr as $class){
					array_push($classes, $class);
				}
			}
		}
		return $classes;
	}

	public static function getAllPossibleIntersectionData(?Datum $datum = null):?array{
		$f = __METHOD__;
		$print = false;
		$intersections = [];
		foreach(static::getIntersections() as $type_hint => $arr){
			if(is_string($arr)){
				array_push($intersections, static::generateIntersectionData($type_hint, null, $datum));
			} else
				foreach($arr as $subtype => $class){
					array_push($intersections, static::generateIntersectionData($type_hint, $subtype, $datum));
				}
		}
		return $intersections;
	}
}