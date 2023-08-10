<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class MultipleElementCommand extends Command implements JavaScriptInterface
{

	public function __construct(...$element_s)
	{
		$f = __METHOD__; //MultipleElementCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		if (! isset($element_s)) {
			Debug::error("{$f} elements are undefined");
		} elseif (count($element_s) === 1 && is_array($element_s)) {
			$element_s = $element_s[0];
		}
		// if(is_array($element_s)){
		/*
		 * if(count($element_s) === 1 && is_int(array_keys($element_s)[0])){
		 * $keys = array_keys($element_s);
		 * parent::__construct($element_s[$keys[0]]);
		 * }else{
		 */
		$this->setElements($element_s);
		// }
		// }//else{//if($element_s instanceof Element){
		// parent::__construct($element_s);
		// }/*else{
		// Debug::print("{$f} neither of the above");
		// }*/
	}

	public function setElements($elements)
	{
		$f = __METHOD__; //MultipleElementCommand::getShortClass()."(".static::getShortClass().")->setElements()";
		// $keys = array_keys($elements);
		// Debug::print("{$f} assigning array with the following keys:");
		// Debug::printArray($keys);
		if (! is_array($elements)) {
			$elements = [
				$elements
			];
		}
		return $this->setArrayProperty("elements", $elements);
	}

	public function hasElements()
	{
		return $this->hasArrayProperty("elements"); // !empty($this->elements);
	}

	public function getElements()
	{
		/*
		 * $f = __METHOD__; //MultipleElementCommand::getShortClass()."(".static::getShortClass().")->getElements()";
		 * if(!$this->hasMultipleElements()){
		 * //Debug::print("{$f} only a single element is defined");
		 * if($this->hasElement()){
		 * return [$this->getElement()];
		 * }
		 * Debug::error("{$f} elements are undefined");
		 * }
		 */
		return $this->getProperty("elements");
	}

	public function getElementCount()
	{
		return $this->getArrayPropertyCount('elements');
	}

	public function setTemplateLoopFlag($value = true)
	{
		$f = __METHOD__; //MultipleElementCommand::getShortClass()."(".static::getShortClass().")->setTemplateLoopFlag()";
		if ($this->hasElements()) {
			foreach ($this->getElements() as $element) {
				if ($element instanceof Element || $element instanceof ElementCommand || $element instanceof MultipleElementCommand) {
					$element->setTemplateLoopFlag($value);
				} else {
					Debug::warning("{$f} element is not an element or element command");
				}
			}
		} else {
			Debug::warning("{$f} element is undefined");
		}
		return false;
	}
}
