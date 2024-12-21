<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;

trait ElementalCommandTrait{

	protected $element;

	protected $id;

	public function setElement($element){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasElement()){
			$this->releaseElement(false);
		}
		if(is_string($element)){
			if($print){
				Debug::print("{$f} element is a string");
			}
			$this->setId($element);
		}elseif($element instanceof Element){
			if($element->hasIdAttribute()){
				$this->setId($element->getIdAttribute());
			}elseif($element->hasAttribute("temp_id")){
				$this->setId($element->getAttribute("temp_id"));
			}
			if($print){
				Debug::print("{$f} element class is " . $element->getClass());
			}
		}elseif($print){
			$gottype = gettype($element);
			Debug::print("{$f} setting a \"{$gottype}\" as an element");
		}
		if($element instanceof HitPointsInterface){
			$that = $this;
			$closure = function(DeallocateEvent $event, HitPointsInterface $target) use ($that){
				$target->removeEventListener($event);
				if($that->hasElement()){
					$that->releaseElement(false);
				}
			};
			$element->addEventListener(EVENT_DEALLOCATE, $closure);
		}
		return $this->element = $this->claim($element);
	}

	public function releaseElement(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasElement()){
			Debug::error("{$f} element is undefined");
		}
		$element = $this->element;
		if(SUPPLEMENTAL_GARBAGE_COLLECTION_ENABLED && !$element->getAllocatedFlag()){
			Debug::error("{$f} element ".$element->getDebugString()." was already deallocated, but never released");
		}
		unset($this->element);
		$this->release($element, $deallocate);
	}
	
	public function getElement(){
		$f = __METHOD__;
		if(!$this->hasElement()){
			Debug::error("{$f} element is undefined for this ".$this->getDebugString());
		}
		return $this->element;
	}

	public function getIdCommandString(){
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasElement()){
				if($print){
					Debug::print("{$f} element is defined");
				}
				$element = $this->getElement();
				if($element instanceof ValueReturningCommandInterface){
					if($print){
						Debug::print("{$f} element is a value-returning command interface");
					}
					return $element;
				}elseif(is_object($element) && $element->hasIdOverride()){
					if($print){
						Debug::print("{$f} element is an object with ID override");
					}
					return $element->getIdOverride();
				}elseif($print){
					Debug::print("{$f} element is not a value-returning command interface, and it does not have ID override");
				}
			}elseif($print){
				Debug::print("{$f} element is undefined");
			}
			return $this->getId();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getId(){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasId()){
			if($print){
				Debug::print("{$f} element ID is undefined");
			}
			if($this instanceof InsertElementCommand){
				Debug::printStackTrace();
			}elseif($this->hasElement()){
				$element = $this->getElement();
				if($element instanceof ValueReturningCommandInterface){
					if($print){
						Debug::print("{$f} element is a value returning command interface");
					}
					// return $element;
				}
				Debug::print("{$f} element is defined");
				$class = $element->getClass();
				Debug::print("{$f} element is class is \"{$class}\"");
				// $element->debugPrintRootElement();
			}else{
				Debug::error("{$f} element is undefined also");
			}
		}
		return $this->id;
	}

	public function setId($id){
		$f = __METHOD__;
		if($this->hasId()){
			$this->release($this->id);
		}
		return $this->id = $this->claim($id);
	}

	public function hasElement():bool{
		return isset($this->element);
	}

	public function hasId():bool{
		$f = __METHOD__;
		return isset($this->id);
	}
}
