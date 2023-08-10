<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

/**
 * Provides a static default element class for use in concert with ElementBindableTrait to save memory
 *
 * @author j
 */
interface StaticElementClassInterface
{

	static function getElementClassStatic(?StaticElementClassInterface $that = null): string;
}
