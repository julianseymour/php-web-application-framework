<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class SetInputValueCommand extends ElementCommand implements ServerExecutableCommandInterface{

	use ValuedTrait;

	public static function getCommandId(): string{
		return "setValue";
	}

	public function getValue(){
		if(!$this->hasValue()){
			return $this->setValue("");
		}
		return $this->value;
	}

	public function __construct($element = null, $value = null){
		$f = __METHOD__;
		$print = false;
		if($print && $element !== null){
			$ec = is_object($element) ? get_class($element) : gettype($element);
			$decl = $element->getDeclarationLine();
			Debug::print("{$f} before parent constructor, element is a(n){$ec}, declared {$decl}");
		}
		parent::__construct($element);
		if($value !== null){
			$this->setValue($value);
		}
		if($print && $this->hasElement()){
			$element = $this->getElement();
			$ec = is_object($element) ? get_class($element) : gettype($element);
			$decl = $element->getDeclarationLine();
			Debug::print("{$f} after constructor, element is a(n){$ec}, declared {$decl}");
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('value', $this->getValue(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasValue()){
			$this->setValue(replicate($that->getValue()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->value, $deallocate);
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$print = false;
		$id = $this->getIdCommandString();
		if($id instanceof JavaScriptInterface){
			$id = $id->toJavaScript();
		}
		$value = $this->getValue();
		if($value instanceof JavaScriptInterface){
			$value = $value->toJavaScript();
		}elseif(is_string($value) || $value instanceof StringifiableInterface){
			$value = single_quote($value);
		}
		$s = "{$id}.value = {$value}";
		if($print){
			Debug::print("{$f} returning \"{$s}\"");
		}
		return $s;
	}

	public function resolve(){
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			$element = $this->getElement();
			if($element === null){
				$decl = $this->getDeclarationLine();
				$did = $this->getDebugId();
				Debug::error("{$f} element is null. Declared {$decl}. Debug ID is {$did}");
			}elseif($print){
				if($element instanceof ValueReturningCommandInterface){
					Debug::print("{$f} element is a value-returning command interface");
				}else{
					Debug::print("{$f} element is NOT a value-returning command interface");
				}
			}
			while($element instanceof ValueReturningCommandInterface){
				$element = $element->evaluate();
			}
			$value = $this->getValue();
			if($print){
				if($value instanceof ValueReturningCommandInterface){
					$did = $value->getDebugString();
					Debug::print("{$f} value is a value-returning command interface {$did}");
				}else{
					Debug::print("{$f} value is NOT a value-returning command interface");
				}
			}
			while($value instanceof ValueReturningCommandInterface){
				$value = $value->evaluate();
			}
			if($print){
				Debug::print("{$f} value resolved itself to \"{$value}\"");
			}
			return $element->setValueAttribute($value);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
