<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

class SetOnPageHideCommand extends SetWindowEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onpagehide";
	}
}
