<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SetClassNameCommand extends ElementCommand implements ServerExecutableCommandInterface{

	protected $className;

	public static function getCommandId(): string{
		return "className";
	}

	public function hasClassName():bool{
		return !empty($this->className);
	}

	public function getClassName(){
		$f = __METHOD__;
		if(!$this->hasClassName()){
			Debug::error("{$f} className is undefined");
		}
		return $this->className;
	}

	public function setClassName($className){
		if($this->hasClassName()){
			$this->release($this->className);
		}
		return $this->className = $this->claim($className);
	}

	public function __construct($element = null, $className = null){
		parent::__construct($element);
		if(!empty($className)){
			$this->setClassName($className);
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('className', $this->getClassName(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasClassName()){
			$this->setClassName(replicate($that->getClassName()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->className, $deallocate);
	}

	public function resolve(){
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$classname = $this->getClassName();
		while($classname instanceof ValueReturningCommandInterface){
			$classname = $classname->evaluate();
		}
		$classes = explode(' ', $classname);
		$element->setClassAttribute(null);
		foreach($classes as $class){
			$element->addClassAttribute($class);
		}
	}

	public function toJavaScript(): string{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface){
			$idcs = $idcs->toJavaScript();
		}
		$className = $this->getClassName();
		if($className instanceof JavaScriptInterface){
			$className = $className->toJavaScript();
		}
		return "{$idcs}.className = {$className};";
	}
}
