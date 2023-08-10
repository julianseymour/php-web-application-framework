<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnClickCommand extends SetElementEventHandlerCommand
{

	public static function getCommandId(): string
	{
		return "onclick";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$call_function = $this->getCallFunctionCommand();
		$element->setOnClickAttribute($call_function);
	}
}
