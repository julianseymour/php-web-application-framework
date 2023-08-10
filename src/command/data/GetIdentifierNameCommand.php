<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class GetIdentifierNameCommand extends DataStructureCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "getIdentifierName";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //GetIdentifierNameCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		$ds = $this->getDataStructure();
		if ($ds instanceof GetForeignDataStructureCommand) {
			$cn = $ds->getColumnName();
			$context = $ds->getDataStructure();
			if (! $context->hasForeignDataStructure($cn)) {
				if ($print) {
					Debug::print("{$f} data structure is returned as the result of a GetForeignDataStructureCommand, and our object does not have a foreign data structure at index \"{$cn}\"; hopefully it has an identifier name defined");
				}
				return $context->getColumn($cn)->getForeignDataIdentifierName();
			}
		}
		while ($ds instanceof ValueReturningCommandInterface) {
			$ds = $ds->evaluate();
		}
		return $ds->getIdentifierName();
	}

	public function toJavaScript(): string
	{
		return $this->evaluate();
	}
}
