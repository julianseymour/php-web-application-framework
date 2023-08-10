<?php
namespace JulianSeymour\PHPWebApplicationFramework\poll;

use JulianSeymour\PHPWebApplicationFramework\command\Command;

class ScheduleUpdateCheckCommand extends Command
{

	public static function getCommandId(): string
	{
		return "scheduleUpdateCheck";
	}
}
