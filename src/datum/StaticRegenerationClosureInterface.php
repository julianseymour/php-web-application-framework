<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use Closure;

/**
 * closure for classes that have a statically defined closure for generating column value
 *
 * @author j
 *        
 */
interface StaticRegenerationClosureInterface
{

	static function getGenerationClosureStatic(object $obj): ?Closure;
}
