<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnAnimationCancelCommand extends SetElementEventHandlerCommand
{

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$element->setOnAnimationCancelAttribute($this->getCallFunctionCommand());
	}

	public static function getCommandId(): string
	{
		return "onanimationcancel";
	}
}
