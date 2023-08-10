<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnInputCommand extends SetElementEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "oninput";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$call_function = $this->getCallFunctionCommand();
		$element->setOnInputAttribute($call_function);
	}
}
