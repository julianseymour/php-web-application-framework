<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnPropertyChangeCommand extends SetElementEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onpropertychange";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$call_function = $this->getCallFunctionCommand();
		$element->setOnPropertyChangeAttribute($call_function);
	}
}
