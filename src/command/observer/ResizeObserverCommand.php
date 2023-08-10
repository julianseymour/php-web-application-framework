<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\observer;

class ResizeObserverCommand extends ObserverCommand
{

	public static function getCommandId(): string
	{
		return "ResizeObserver";
	}
}
