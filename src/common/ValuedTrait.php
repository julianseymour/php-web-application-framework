<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ValuedTrait{

	protected $value;

	public function getValue(){
		$f = __METHOD__;
		if(!$this->hasValue()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} value is undefined; declared {$decl}");
		}
		return $this->value;
	}

	public function setValue($value){
		if($this->hasValue()){
			$this->release($this->value);
		}
		return $this->value = $this->claim($value);
	}
	
	public function hasValue():bool{
		return isset($this->value);
	}
}
