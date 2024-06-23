<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\ElementTagTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use JulianSeymour\PHPWebApplicationFramework\common\TypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;

class CreateElementCommand extends Command implements AllocationModeInterface, JavaScriptInterface, ValueReturningCommandInterface{

	use AllocationModeTrait;
	use ElementBindableTrait;
	use ElementTagTrait;
	use TypeTrait;
	
	public static function getCommandId(): string{
		return "creatElement";
	}

	public function __construct($tag = null, $type = null, $mode = null){
		$f = __METHOD__;
		parent::__construct();
		if(isset($tag)){
			if(is_string($tag) && is_a($tag, Element::class, true)){
				$this->setElementClass($tag);
				if(method_exists($tag, "getElementTagStatic")){
					$tag = $tag::getElementTagStatic();
				}else{
					Debug::error("{$f} element class does not have a static tag");
				}
			}
			$this->setElementTag($tag);
		}
		if(isset($type)){
			$this->setType($type);
		}
		if(isset($mode)){
			$this->setAllocationMode($mode);
		}
	}

	public function getElementTag():string{
		$f = __METHOD__;
		if(isset($this->tag)){
			return $this->tag;
		}elseif(!$this->hasElementClass()){
			Debug::error("{$f} element tag and class are undefined");
		}
		$ec = $this->getElementClass();
		if(!method_exists($ec, "getElementTagStatic")){
			Debug::error("{$f} cannot statically determine tag from element class \"{$ec}\" for this ".$this->getDebugString());
		}
		return $ec::getElementTagStatic();
	}
	
	public function toJavaScript(): string{
		$tag = $this->getElementTag();
		if($tag instanceof JavaScriptInterface){
			$tag = $tag->toJavaScript();
		}elseif(is_string($tag) || $tag instanceof StringifiableInterface){
			$tag = single_quote($tag);
		}
		return "document.createElement({$tag})";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$print = false;
		if($this->hasAllocationMode()){
			$mode = $this->getAllocationMode();
			while($mode instanceof ValueReturningCommandInterface){
				$mode = $mode->evaluate();
			}
		}else{
			$mode = ALLOCATION_MODE_UNDEFINED;
		}
		if($this->hasElementClass()){
			$ec = $this->getElementClass();
			while($ec instanceof ValueReturningCommandInterface){
				$ec = $ec->evaluate();
			}
			return new $ec($mode);
		}
		$tag = $this->getElementTag();
		if($tag instanceof ValueReturningCommandInterface){
			while($tag instanceof ValueReturningCommandInterface){
				$tag = $tag->evaluate();
			}
		}
		if($this->hasType()){
			$type = $this->getType();
			if($type instanceof ValueReturningCommandInterface){
				while($type instanceof ValueReturningCommandInterface){
					$type = $type->evaluate();
				}
			}
		}else{
			$type = null;
		}
		return Document::createElement($tag, $type, $mode);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->allocationMode, $deallocate);
		$this->release($this->tag, $deallocate);
		$this->release($this->type, $deallocate);
	}
}
