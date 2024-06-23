<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class UncheckInputCommand extends ElementCommand implements ServerExecutableCommandInterface{

	public static function getCommandId(): string{
		return "uncheck";
	}

	public function toJavaScript(): string{
		$id = $this->getIdCommandString();
		if($id instanceof JavaScriptInterface){
			$id = $id->toJavaScript();
		}
		return "{$id}.checked = false";
	}

	public function resolve(){
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		if($element->hasCheckedAttribute()){
			$element->removeAttribute("checked");
		}
	}
}
