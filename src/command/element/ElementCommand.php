<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class ElementCommand extends Command implements JavaScriptInterface{

	use ElementalCommandTrait;

	public function getDebugSubcommandString(){
		$ret = parent::getDebugSubcommandString();
		if($this->hasId()) {
			$ret .= "\"" . $this->getId() . "\"";
		}
		return $ret;
	}

	public function __construct($element){
		$f = __METHOD__;
		$print = false;
		parent::__construct();
		if(is_string($element)) {
			if($print) {
				Debug::print("{$f} element is a string -- setting ID");
			}
			$this->setId($element);
			$this->setElement($element);
		}elseif($element instanceof Element) {
			if($element->getDeletedFlag()) {
				Debug::error("{$f} element has already been deleted");
			}elseif($print) {
				Debug::print("{$f} element is an element");
			}
			$this->setElement($element);
		}elseif($element instanceof ValueReturningCommandInterface) {
			if($print) {
				Debug::print("{$f} element is another command");
			}
			$this->setElement($element);
			if($element instanceof ConcatenateCommand) {
				$this->setId($element);
			}elseif($element instanceof GetElementByIdCommand) {
				$this->setId($element->getId());
			}
		}elseif($print) {
			Debug::error("{$f} element is not string, element or media command");
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		if($this->hasId()) {
			Json::echoKeyValuePair('id', $this->getId(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->id);
	}

	public function setTemplateLoopFlag($value = true){
		$f = __METHOD__;
		if($this->hasElement()) {
			if($this->element instanceof Element || $this->element instanceof ElementCommand || $this->element instanceof MultipleElementCommand) {
				return $this->getElement()->setTemplateLoopFlag($value);
			}
			Debug::warning("{$f} element is not an element or element media command");
		}else{
			Debug::warning("{$f} element is undefined");
		}
		return false;
	}
}
