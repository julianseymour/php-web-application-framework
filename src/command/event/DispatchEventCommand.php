<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class DispatchEventCommand extends ElementCommand implements ServerExecutableCommandInterface{

	Use ParametricTrait;

	private $event;

	public static function getCommandId(): string{
		return "dispatchEvent";
	}

	public function setEvent($event){
		if($this->hasEvent()){
			$this->release($this->event);
		}
		return $this->event = $this->claim($event);
	}

	public function hasEvent():bool{
		return !empty($this->event);
	}

	public function getEvent(){
		$f = __METHOD__;
		if(!$this->hasEvent()){
			Debug::error("{$f} event is undefined");
		}
		return $this->event;
	}

	public function __construct($element=null, $event=null, ...$parameters){
		parent::__construct($element);
		if($event !== null){
			$this->setEvent($event);
		}
		if(isset($parameters)){
			foreach($parameters as $p){
				$this->pushParameters($p);
			}
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('event', $this->getEvent(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->event, $deallocate);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasEvent()){
			$this->setEvent(replicate($that->getEvent()));
		}
		return $ret;
	}
	
	public function resolve(){
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$event = $this->getEvent();
		while($event instanceof ValueReturningCommandInterface){
			$event = $event->evaluate();
		}
		$params = $this->getParameters();
		$element->dispatchEvent($event, ...$params);
	}

	public function toJavaScript(): string{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface){
			$idcs = $idcs->toJavaScript();
		}
		$event = $this->getEvent();
		if($event instanceof JavaScriptInterface){
			$event = $event->toJavaScript();
		}elseif(is_string($event) || $event instanceof StringifiableInterface){
			$q = $this->getQuoteStyle();
			$event = "{$q}" . escape_quotes($event, $q) . "{$q}";
		}
		return "{$idcs}.dispatchEvent(new Event({$event}))";
	}
}
