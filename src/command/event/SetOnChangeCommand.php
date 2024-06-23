<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnChangeCommand extends SetElementEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onchange";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$call_function = $this->getCallFunctionCommand();
		$element->setOnChangeAttribute($call_function);
	}
}
