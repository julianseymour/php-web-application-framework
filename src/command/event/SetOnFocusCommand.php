<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnFocusCommand extends SetElementEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onfocus";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$call_function = $this->getCallFunctionCommand();
		$element->setOnFocusAttribute($call_function);
	}
}
