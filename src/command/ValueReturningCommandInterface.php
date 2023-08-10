<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

/**
 * Commands that have an evaluate() function, which returns a value.
 *
 * @author j
 *        
 */
interface ValueReturningCommandInterface
{

	public function evaluate(?array $params = null);
	// public function getReturnType();
}
