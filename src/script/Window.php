<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\command\event\AddEventListenerCommand;

class Window{

	public static function addEventListener(string $type, $listener):AddEventListenerCommand{
		return new AddEventListenerCommand('window', $type, $listener);
	}
}
