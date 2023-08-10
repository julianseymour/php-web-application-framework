<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\Command;

class InitializeAllFormsCommand extends Command
{

	public static function getCommandId(): string
	{
		return "initializeAllForms";
	}
}
