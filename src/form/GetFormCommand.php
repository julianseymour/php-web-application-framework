<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;

class GetFormCommand extends ElementCommand implements ServerExecutableCommandInterface, ValueReturningCommandInterface{

	public function toJavaScript(): string{
		$ids = $this->getIdCommandString();
		return "{$ids}.form";
	}

	public static function getCommandId(): string{
		return "form";
	}

	public function resolve(){
		return $this->evaluate();
	}

	public function evaluate(?array $params = null){
		return $this->getElement()->getForm();
	}
}
