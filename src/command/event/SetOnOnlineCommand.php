<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class SetOnOnlineCommand extends SetWindowEventHandlerCommand
{

	public function resolve()
	{
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$element->setOnOnlineAttribute($this->getCallFunctionCommand());
	}

	public static function getCommandId(): string
	{
		return "ononline";
	}
}
