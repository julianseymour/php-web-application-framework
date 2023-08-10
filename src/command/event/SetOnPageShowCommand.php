<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

class SetOnPageShowCommand extends SetWindowEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onpageshow";
	}
}
