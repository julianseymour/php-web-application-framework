<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

class SetOnPopStateCommand extends SetWindowEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onpopstate";
	}
}
