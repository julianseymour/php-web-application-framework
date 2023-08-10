<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnKeyDownCommand extends SetElementEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onkeydown";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$call_function = $this->getCallFunctionCommand();
		$element->setOnKeyDownAttribute($call_function);
	}
}
