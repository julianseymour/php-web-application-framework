<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetAttributeCommand extends ElementCommand implements ValueReturningCommandInterface{

	protected $attributeName;

	public function __construct($element=null, $attr_name=null){
		$f = __METHOD__;
		parent::__construct($element);
		if($attr_name !== null){
			$this->setAttributeName($attr_name);
		}
	}

	public function setAttributeName($attr_name){
		if($this->hasAttributeName()){
			$this->release($this->attributeName);
		}
		return $this->attributeName = $this->claim($attr_name);
	}

	public function hasAttributeName():bool{
		return isset($this->attributeName);
	}

	public function getAttributeName(){
		$f = __METHOD__;
		if(!$this->hasAttributeName()){
			Debug::error("{$f} attribute name is undefined");
		}
		return $this->attributeName;
	}

	public function toJavaScript(): string{
		$idc = $this->getIdCommandString();
		if($idc instanceof JavaScriptInterface){
			$idc = $idc->toJavaScript();
		}
		$attr_name = $this->getAttributeName();
		if($attr_name instanceof JavaScriptInterface){
			$attr_name = $attr_name->toJavaScript();
		}elseif(is_string($attr_name) || $attr_name instanceof StringifiableInterface){
			$attr_name = single_quote($attr_name);
		}
		return "{$idc}.getAttribute({$attr_name})";
	}

	public static function getCommandId(): string{
		return "getAttribute";
	}

	public function evaluate(?array $params = null){
		$attr_name = $this->getAttributeName();
		while($attr_name instanceof ValueReturningCommandInterface){
			$attr_name = $attr_name->evaluate();
		}
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		return $element->getAttribute($attr_name);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->attributeName, $deallocate);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasAttributeName()){
			$this->setAttributeName(replicate($that->getAttributeName()));
		}
		return $ret;
	}
}
