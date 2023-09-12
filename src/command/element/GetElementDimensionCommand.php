<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class GetElementDimensionCommand extends ElementCommand implements ValueReturningCommandInterface
{

	public function toJavaScript(): string
	{
		$f = __METHOD__; //GetElementDimensionCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$e = $this->getIdCommandString();
		if($e instanceof JavaScriptInterface) {
			$e = $e->toJavaScript();
		}
		$command = $this->getCommandId();
		return "{$e}.{$command}";
	}
}
