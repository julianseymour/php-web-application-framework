<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnBeforeUnloadCommand extends SetElementEventHandlerCommand
{

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$element->setOnBeforeUnloadAttribute($this->getCallFunctionCommand());
	}

	public static function getCommandId(): string
	{
		return "onbeforeunload";
	}
}
