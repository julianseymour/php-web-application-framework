<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

/**
 * interface for objects that can be converted to a SQL command or fragment
 *
 * @author j
 *        
 */
interface SQLInterface
{

	public function toSQL(): string;
}
