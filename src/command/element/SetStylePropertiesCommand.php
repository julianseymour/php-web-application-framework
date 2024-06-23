<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use JulianSeymour\PHPWebApplicationFramework\style\CssProperty;
use Exception;

class SetStylePropertiesCommand extends ElementCommand implements ServerExecutableCommandInterface{

	public static function getCommandId(): string{
		return "style";
	}

	public function setStyleProperties($properties){
		$f = __METHOD__;
		$print = false;
		if(!isset($properties) || !is_array($properties) || empty($properties) || count($properties) < 1){
			if(is_array($properties)){
				Debug::printArray($properties);
				Debug::error("{$f} see above for invalid array passed to this function");
			}elseif(is_object($properties)){
				Debug::error("{$f} invalid parameter is a ".$ds->getDebugString());
			}else{
				$gottype = gettype($properties);
				Debug::error("{$f} invalid parameter type {$gottype}");
			}
		}elseif($print){
			Debug::print("{$f} OK");
		}
		return $this->setArrayProperty("style", $properties);
	}

	/**
	 *
	 * @return string[]|CssProperty[]
	 */
	public function getStyleProperties(){
		return $this->getProperty('style');
	}

	public function hasStyleProperties():bool{
		return $this->hasArrayProperty("style");
	}
	
	public function __construct($element=null, $properties=null){
		$f = __METHOD__;
		try{
			$print = false;
			parent::__construct($element);
			if($properties !== null){
				if($print){
					Debug::print("{$f} about to set style properties...");
				}
				$this->setStyleProperties($properties);
			}elseif($print){
				Debug::print("{$f} style properties are undefined");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		try{
			$properties = [];
			foreach($this->getStyleProperties() as $name => $property){
				if($property instanceof CssProperty){
					$value = $property->getValueString();
				}else{
					$value = $property;
				}
				$properties[$name] = $value;
			}
			Json::echoKeyValuePair('properties', $properties, $destroy);
			parent::echoInnerJson($destroy);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$string = "";
			$e = $this->getIdCommandString();
			if($e instanceof JavaScriptInterface){
				$e = $e->toJavaScript();
			}
			$i = 0;
			if(!$this->hasStyleProperties()){
				$ds = $this->getDebugString();
				Debug::error("{$f} {$ds} has no style properties");
			}
			foreach($this->getStyleProperties() as $name => $property){
				if($property instanceof JavaScriptInterface){
					$value = $property->toJavaScript();
				}elseif($property instanceof CssProperty){
					$value = $property->getValueString();
				}elseif(is_string($property) || $property instanceof StringifiableInterface){
					$value = single_quote($property);
				}else{
					$value = $property;
				}
				if($i ++ > 0){
					$string .= ";\n";
				}
				$string .= "{$e}.style['{$name}'] = {$value}";
			}
			return $string;
		}catch(Exception $x){
			X($f, $x);
		}
	}

	public function resolve(){
		$f = __METHOD__;
		try{
			$element = $this->getElement();
			while($element instanceof ValueReturningCommandInterface){
				$element = $element->evaluate();
			}
			foreach($this->getStyleProperties() as $name => $value){
				if($value instanceof CssProperty){
					$value = $value->getValueString();
				}
				$element->setStyleProperties([
					$name => $value
				]);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
	