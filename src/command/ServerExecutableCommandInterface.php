<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

/**
 * Commands with the resolve() function, which is called by a/o Element->resolveTemplateCommand.
 * Typically void function used to set the values of templated local variables in conditional/switch
 * statements in server-rendered elements that are also templateworthy.
 * These commands can also be converted to PHP closures
 *
 * @author j
 *        
 */
interface ServerExecutableCommandInterface
{

	public function resolve();

	public function toClosure();
}
