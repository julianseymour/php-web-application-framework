<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class JavaScriptFunctionGenerator extends Basic{

	protected $generatedFunction;
	
	public abstract function generate($context):?JavaScriptFunction;

	public function hasGeneratedFunction(): bool{
		return isset($this->generatedFunction);
	}

	public function setGeneratedFunction(?JavaScriptFunction $fn): ?JavaScriptFunction{
		if($this->hasGeneratedFunction()){
			$this->release($this->generatedFunction);
		}
		return $this->generatedFunction = $this->claim($fn);
	}

	public function getGeneratedFunction(): JavaScriptFunction{
		$f = __METHOD__;
		if(!$this->hasGeneratedFunction()){
			Debug::error("{$f} generated function is undefined");
		}
		return $this->generatedFunction;
	}

	// XXX TODO have a parameter name list to map parameters to variable names
	public function evaluate($context, $params){
		$f = __METHOD__;
		$print = false;
		if($print){
			if(isset($params)){
				Debug::print("{$f} about to evaluate function for the following parameters:");
				Debug::printArray($params);
			}else{
				Debug::print("{$f} no parameters");
			}
		}
		if($this->hasGeneratedFunction()){
			return $this->getGeneratedFunction()->evaluate($params);
		}
		return $this->setGeneratedFunction($this->generate($context))->evaluate($params);
	}

	public function debugPrint($context):void{
		$f = __METHOD__;
		if(!$this->hasGeneratedFunction()){
			Debug::print("{$f} did not generate function yet");
			$this->setGeneratedFunction($this->generate($context));
		}
		$this->getGeneratedFunction()->debugPrint();
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->generatedFunction, $deallocate);
	}
}
