<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class GetDataStructureCommand extends DataStructureCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "getDataStructure";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //GetDataStructureCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		ErrorMessage::unimplemented($f);
	}

	public function evaluate(?array $params = null)
	{
		return $this->getDataStructure();
	}
}
