<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnBlurCommand extends SetElementEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onblur";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$call_function = $this->getCallFunctionCommand();
		$element->setOnBlurAttribute($call_function);
	}
}
