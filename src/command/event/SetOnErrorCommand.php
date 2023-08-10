<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnErrorCommand extends SetElementEventHandlerCommand
{

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$element->setOnErrorAttribute($this->getCallFunctionCommand());
	}

	public static function getCommandId(): string
	{
		return "onerror";
	}
}
