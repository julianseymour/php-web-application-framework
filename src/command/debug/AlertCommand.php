<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\debug;

class AlertCommand extends LogCommand
{

	public static function getCommandId(): string
	{
		return "window.alert";
	}
}
