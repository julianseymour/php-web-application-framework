<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

trait StaticPropertyTypeTrait
{

	use PropertiesTrait;

	public abstract static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array;

	public static function getPropertyTypeStatic($key, ?StaticPropertyTypeInterface $object = null)
	{
		$types = static::declarePropertyTypes($object);
		if (array_key_exists($key, $types)) {
			return $types[$key];
		}
		return null;
	}
}
