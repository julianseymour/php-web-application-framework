<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

use JulianSeymour\PHPWebApplicationFramework\command\RoutineTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\IfExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class DropRoutineStatement extends QueryStatement
{

	use IfExistsFlagBearingTrait;
	use NamedTrait;
	use RoutineTypeTrait;

	public function __construct(?string $type = null, ?string $name = null)
	{
		parent::__construct();
		if ($type !== null) {
			$this->setRoutineType($type);
		}
		if ($name !== null) {
			$this->setName($name);
		}
	}

	public static function dropFunction(?string $name = null): DropRoutineStatement
	{
		return new DropRoutineStatement(ROUTINE_TYPE_FUNCTION, $name);
	}

	public static function dropFunctionIfExists(?string $name = null): DropRoutineStatement
	{
		return static::dropFunction($name)->ifExists();
	}

	public static function dropProcedure(?string $name = null): DropRoutineStatement
	{
		return new DropRoutineStatement(ROUTINE_TYPE_PROCEDURE, $name);
	}

	public static function dropProcedureIfExists(?string $name = null): DropRoutineStatement
	{
		return static::dropProcedure($name)->ifExists();
	}

	public function getQueryStatementString()
	{
		// DROP {PROCEDURE | FUNCTION} [IF EXISTS] sp_name
		$string = "drop " . $this->getRoutineType() . " ";
		if ($this->getIfExistsFlag()) {
			$string .= "if exists ";
		}
		$string .= $this->getName();
		return $string;
	}
}
