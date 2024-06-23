<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class MultipleElementCommand extends Command implements JavaScriptInterface{

	public function __construct(...$element_s){
		$f = __METHOD__;
		parent::__construct();
		if(isset($element_s) && count($element_s) > 0){
			if(count($element_s) === 1 && is_array($element_s[0])){
				$element_s = $element_s[0];
			}
			$this->setElements($element_s);
		}
	}

	public function setElements($elements){
		if(!is_array($elements)){
			$elements = [
				$elements
			];
		}
		return $this->setArrayProperty("elements", $elements);
	}

	public function hasElements():bool{
		return $this->hasArrayProperty("elements");
	}

	public function getElements(){
		return $this->getProperty("elements");
	}

	public function getElementCount():int{
		return $this->getArrayPropertyCount('elements');
	}

	public function setTemplateLoopFlag(bool $value = true):bool{
		$f = __METHOD__;
		if($this->hasElements()){
			foreach($this->getElements() as $element){
				if($element instanceof Element || $element instanceof ElementCommand || $element instanceof MultipleElementCommand){
					$element->setTemplateLoopFlag($value);
				}else{
					Debug::warning("{$f} element is not an element or element command");
				}
			}
		}else{
			Debug::warning("{$f} element is undefined");
		}
		return $value;
	}
}
