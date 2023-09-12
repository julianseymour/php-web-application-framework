<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait RoutineTypeTrait
{

	protected $routineType;

	public function setRoutineType(?string $type): ?string
	{
		$f = __METHOD__; //"RoutineTypeTrait(".static::getShortClass().")->setRoutineType()";
		if($type == null) {
			unset($this->routineType);
			return null;
		}
		$type = strtolower($type);
		switch ($type) {
			case ROUTINE_TYPE_FUNCTION:
			case ROUTINE_TYPE_PROCEDURE:
			case ROUTINE_TYPE_STATIC:
			case ROUTINE_TYPE_CONST:
			case ROUTINE_TYPE_NONE:
				break;
			default:
				Debug::error("{$f} invalid routine type \"{$type}\"");
		}
		return $this->routineType = $type;
	}

	public function hasRoutineType(): bool
	{
		return isset($this->routineType) && is_string($this->routineType) && ! empty($this->routineType);
	}

	public function getRoutineType(): string
	{
		$f = __METHOD__; //"RoutineTypeTrait(".static::getShortClass().")->getRoutineType()";
		if(!$this->hasRoutineType()) {
			Debug::error("{$f} routine type is undefined");
		}
		return $this->routineType;
	}
}
