<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnTransitionRunCommand extends SetElementEventHandlerCommand
{

	public function resolve()
	{
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$element->setOnTransitionRunAttribute($this->getCallFunctionCommand());
	}

	public static function getCommandId(): string
	{
		return "ontransitionrun";
	}
}
