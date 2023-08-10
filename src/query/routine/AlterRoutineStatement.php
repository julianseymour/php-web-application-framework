<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

use JulianSeymour\PHPWebApplicationFramework\command\RoutineTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLSecurityTrait;

class AlterRoutineStatement extends RoutineStatement
{

	use RoutineTypeTrait;
	use SQLSecurityTrait;

	public function getQueryStatementString(): string
	{
		// ALTER FUNCTION func_name [characteristic ...]
		return "alter " . $this->getRoutineType() . " " . $this->getName() . " " . $this->getCharacteristics();
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->routineType);
		unset($this->sqlSecurityType);
	}
}
