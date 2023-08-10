<?php
namespace JulianSeymour\PHPWebApplicationFramework\core;

/**
 * used to determine which class to instantiate when loading foreign data structures etc
 *
 * @author j
 */
abstract class ClassResolver extends Basic
{

	public abstract static function resolveClass($datum);
}
