<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementalCommandTrait;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;

abstract class SetElementEventHandlerCommand extends SetEventHandlerCommand implements ServerExecutableCommandInterface{

	use ElementalCommandTrait;

	public function __construct($element=null, $call_function=null){
		$f = __METHOD__;
		parent::__construct($call_function);
		if($element !== null){
			if(is_string($element)){
				$this->setId($element);
				$this->setElement($element);
			}elseif($element instanceof Element){
				if($element->getDeletedFlag()){
					Debug::error("{$f} element has already been deleted");
				}
				$this->setElement($element);
			}elseif($element instanceof ValueReturningCommandInterface){
				// Debug::print("{$f} element is another command");
				$this->setElement($element);
				if($element instanceof ConcatenateCommand){
					$this->setId($element);
				}
			}else{
				Debug::error("{$f} element is not string, element or media command");
			}
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasElement()){
			$this->releaseElement(false);
		}
		parent::dispose($deallocate);
	}
}
