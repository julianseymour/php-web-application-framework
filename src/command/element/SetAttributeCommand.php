<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\Attribute;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class SetAttributeCommand extends ElementCommand implements ServerExecutableCommandInterface{

	/**
	 *
	 * @param Element|string $element
	 * @param array $attributes
	 *        	: list of key value pairs
	 */
	public function __construct($element=null, $attributes=null){
		$f = __METHOD__;
		parent::__construct($element);
		if(!empty($attributes)){
			if(!is_array($attributes)){
				Debug::printStackTraceNoExit("{$f} received something that is not an array");
			}
			$this->setAttributes($attributes);
		}
	}

	public function getAttributes(){
		return $this->getProperty("attributes");
	}

	public function hasAttributes():bool{
		return $this->hasArrayProperty("attributes");
	}

	public function getAttributeCount(){
		return $this->getArrayPropertyCount("attributes");
	}

	public function setAttributes($attr){
		$f = __METHOD__;
		foreach($attr as $key => $value){
			if($value instanceof UseCase){
				Debug::error("{$f} attempting to set attribute \"{$key}\" to a UseCase");
			}
		}
		return $this->setArrayProperty("attributes", $attr);
	}

	public function hasAttribute($key):bool{
		return $this->hasAttributes() && array_key_exists($key, $this->attributes);
	}

	public function getAttribute($key){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasAttribute($key)){
			Debug::error("{$f} attribute \"{$key}\" is undefined");
		}
		$attr = $this->getArrayPropertyValue("attributes", $key);
		if($print){
			Debug::print("{$f} returning \"{$attr}\"");
		}
		return $attr;
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Json::echoKeyValuePair("id", $this->getId());
		Json::echoKeyValuePair("attributes", $this->getAttributes(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public static function getCommandId(): string{
		return "setAttributes";
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$string = "";
			$id = $this->getIdCommandString();
			if($id instanceof JavaScriptInterface){
				$id = $id->toJavaScript();
			}
			$i = 0;
			foreach($this->getAttributes() as $key => $value){
				if(is_object($value)){
					if($value instanceof UseCase){
						Debug::error("{$f} somehow attempting to set a use case as an attribute value");
					}elseif($value instanceof Attribute){
						$key = single_quote($value->getName());
						$value = $value->toJavaScript();
					}elseif($value instanceof JavaScriptInterface){
						$value = $value->toJavaScript();
					}elseif($value instanceof StringifiableInterface){
						$value = single_quote($value);
					}else{
						$avc = $value->getClass();
						Debug::error("{$f} attribute value is an object of class \"${avc}\"");
					}
				}elseif(is_string($value) || $value === null){
					if($value === null){
						$value = "";
					}
					if($print){
						Debug::print("{$f} attribute \"{$key}\" has string value \"{$value}\"");
					}
					$value = single_quote($value);
				}
				if($i ++ > 0){
					$string .= ";\n";
				}
				$string .= "{$id}.setAttribute('{$key}', {$value})";
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function resolve(){
		$f = __METHOD__;
		try{
			$element = $this->getElement();
			foreach($this->getAttributes() as $key => $value){
				while($value instanceof Command){
					$value = $value->evaluate();
				}
				$element->setAttribute($key, $value);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
