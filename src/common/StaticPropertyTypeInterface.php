<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

/**
 * Classes that implements this interface should also use StaticPropertyTypeTrait
 *
 * @author j
 *        
 */
interface StaticPropertyTypeInterface{

	static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null):array;

	static function getPropertyTypeStatic($key, ?StaticPropertyTypeInterface $object = null); // implemented by StaticPropertyTypeTrait
}
