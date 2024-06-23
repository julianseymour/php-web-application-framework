<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\VariableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class AppendChildCommand extends InsertChildCommand{

	use VariableNameTrait;
	
	public static function getInsertWhere(){
		return "appendChild";
	}

	public function dispose(bool $deallocate=false):void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($print){
			Debug::print("{$f} entered for ".$this->getDebugString().". About to call parent function");
		}
		parent::dispose($deallocate);
		if($print){
			Debug::print("{$f} returned from parent function");
		}
		$this->release($this->variableName, $deallocate);
	}
	
	public function setParentNode($node){
		$ret = parent::setParentNode($node);
		if($node instanceof Element && $node->hasIdOverride()){
			$this->setVariableName($node->getIdOverride());
		}
		return $ret;
	}
	
	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->getElementCount() > 1){
				Debug::error("{$f} unimplemented: insert multiple elements");
			}elseif($this->hasVariableName()){
				$id = $this->getVariableName();
			}elseif($this->hasReferenceElementId()){
				$id = $this->getReferenceElementId();
			}elseif($this->hasParentNode()){
				//Debug::error("{$f} this path has been disabled to allow us to deallocate the parent node. Instantiated ".$this->getDebugString());
				$parent = $this->getParentNode();
				if($parent instanceof Element){
					if(!$parent->hasIdOverride()){
						$ds1 = $parent->getDebugString();
						$ds2 = $this->getDebugString();
						Debug::error("{$f} variable name is undefined for parent element {$ds1}. This command is a {$ds2}.");
					}
					$id = $parent->getIdOverride();
				}else{
					Debug::error("{$f} parent is not an element");
				}
			}elseif($this->hasInsertHere()){
				
			}else{
				$ds = $this->getDebugString();
				Debug::error("{$f} none of the above for {$ds}");
			}
			if($id instanceof JavaScriptInterface){
				$id = $id->toJavaScript();
			}
			if($print){
				Debug::print("{$f} insertion target ID is \"{$id}\"");
			}
			$elements = $this->getElements();
			$element = $elements[array_keys($elements)[0]];
			if($element instanceof ValueReturningCommandInterface){
				$append_me = $element;
			}elseif(is_object($element) && $element->hasIdOverride()){
				$append_me = $element->getIdOverride();
			}else{
				$gottype = is_object($element) ? $element->getClass() : gettype($element);
				Debug::error("{$f} element is not a value-returning command, and does not have an ID override; type is \"{$gottype}\"");
			}
			if($append_me instanceof JavaScriptInterface){
				$append_me = $append_me->toJavaScript();
			}
			$s = "";
			// $s .= CommandBuilder::log("About to append something called \"{$append_me}\"");
			// $s .= ";\n\t";
			$s .= "{$id}.appendChild({$append_me})";
			return $s;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function resolve(){
		$f = __METHOD__;
		$print = false;
		$elements = $this->getElements();
		foreach($elements as $element){
			while($element instanceof ValueReturningCommandInterface){
				if($print){
					$ec = $element->getClass();
					Debug::print("{$f} element is a value returning media command of class \"{$ec}\"");
				}
				$element = $element->evaluate();
			}
			$this->getParentNode()->appendChild($element);
		}
		return $elements;
	}

	public function evaluate(?array $params = null){
		return $this->resolve();
	}
}
