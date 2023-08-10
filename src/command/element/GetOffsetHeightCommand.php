<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

class GetOffsetHeightCommand extends GetElementDimensionCommand
{

	public static function getCommandId(): string
	{
		return "offsetHeight";
	}

	public function evaluate(?array $params = null)
	{
		$idcs = $this->getIdCommandString();
		return "{$idcs}.offsetHeight";
	}
}
