<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetInputValueCommand extends ElementCommand implements ValueReturningCommandInterface{

	public static function getCommandId(): string{
		return "getValue";
	}

	public function __construct($element = null, $parseType = null){
		parent::__construct($element);
		if(isset($parseType)){
			$this->setParseType($parseType);
		}
	}

	public function toJavaScript(): string{
		$id = $this->getIdCommandString();
		if($id instanceof JavaScriptInterface){
			$id = $id->toJavaScript();
		}
		return "{$id}.value";
	}

	public function evaluate(?array $params = null){
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		return $element->getValueAttribute();
	}
}
