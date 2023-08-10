<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

class SetOnOfflineCommand extends SetWindowEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onoffline";
	}
}
