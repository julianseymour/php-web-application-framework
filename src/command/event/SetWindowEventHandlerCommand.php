<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class SetWindowEventHandlerCommand extends SetEventHandlerCommand
{

	public final function resolve()
	{
		$f = __METHOD__; //SetWindowEventHandlerCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		Debug::error("{$f} window events cannot be resolved server-side");
	}

	public final function getIdCommandString()
	{
		return "window";
	}
}
