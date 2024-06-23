<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait VariableNameTrait{

	protected $variableName;

	public function setVariableName($vn){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($print){
			$ds = $this->getDebugString();
			Debug::print("{$f} entered for this {$ds}");
		}
		if($this->hasVariableName()){
			$this->release($this->variableName);
		}
		return $this->variableName = $this->claim($vn);
	}

	public function hasVariableName():bool{
		return isset($this->variableName);
	}

	public function getVariableName(){
		$f = __METHOD__;
		if(!$this->hasVariableName()){
			$ds = $this->getDebugString();
			Debug::error("{$f} variable name is undefined for this {$ds}");
		}
		return $this->variableName;
	}
}
