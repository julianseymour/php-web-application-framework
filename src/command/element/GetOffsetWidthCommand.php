<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

class GetOffsetWidthCommand extends GetElementDimensionCommand
{

	public static function getCommandId(): string
	{
		return "offsetWidth";
	}

	public function evaluate(?array $params = null)
	{
		$idcs = $this->getIdCommandString();
		return "{$idcs}.offsetWidth";
	}
}
