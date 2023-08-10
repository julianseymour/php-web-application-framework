<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\command\event\AddEventListenerCommand;

class Window
{

	public static function addEventListener($type, $listener)
	{
		return new AddEventListenerCommand('window', $type, $listener);
	}
}
