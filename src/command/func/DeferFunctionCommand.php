<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\func;

class DeferFunctionCommand extends InvokeFunctionCommand
{

	public static function getCommandId(): string
	{
		return "defer";
	}
}
