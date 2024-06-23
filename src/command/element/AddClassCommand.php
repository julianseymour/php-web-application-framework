<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class AddClassCommand extends ElementCommand implements ServerExecutableCommandInterface{

	public static function getCommandId(): string{
		return "addClass";
	}

	public function __construct($element=null, ...$added_classes){
		parent::__construct($element);
		if(isset($added_classes) && count($added_classes) > 0){
			$this->setClassNames($added_classes);
		}
	}

	public function setClassNames($added_classes){
		return $this->setArrayProperty("classNames", $added_classes);
	}

	public function hasClassNames():bool{
		return $this->hasArrayProperty("classNames");
	}

	public function getClassNames(){
		$f = __METHOD__;
		if(!$this->hasClassNames()){
			Debug::error("{$f} className is undefined");
		}
		return $this->getProperty("classNames");
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair("classNames", $this->getClassNames());
		parent::echoInnerJson($destroy);
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$e = $this->getIdCommandString();
			if($e instanceof JavaScriptInterface){
				$e = $e->toJavaScript();
			}
			$str = "";
			foreach($this->getClassNames() as $cn){
				if($cn instanceof JavaScriptInterface){
					$cn = $cn->toJavaScript();
				}elseif(is_string($cn) || $cn instanceof StringifiableInterface){
					$cn = "\"" . escape_quotes($cn, QUOTE_STYLE_DOUBLE) . "\"";
				}
				if($str !== ""){
					$str .= ",";
				}
				$str = $cn;
			}
			return "{$e}.classList.add({$str})";
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function resolve(){
		$f = __METHOD__;
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$classes = $this->getClassNames();
		$add_us = [];
		foreach($classes as $class){
			while($class instanceof ValueReturningCommandInterface){
				$class = $class->evaluate();
			}
			array_push($add_us, $class);
		}
		$element->addClassAttribute(...$add_us);
		return SUCCESS;
	}
}
