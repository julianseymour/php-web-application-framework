<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait NullableValuedTrait{
	
	use ValuedTrait;
	
	public function setNullFlag(bool $value = true): bool{
		return $this->setFlag("null", $value);
	}
	
	public function getNullFlag(): bool{
		return $this->getFlag("null");
	}
	
	public function hasValue():bool{
		return isset($this->value) || $this->getNullFlag();
	}
	
	public function getValue(){
		$f = __METHOD__;
		if(!isset($this->value)){
			if($this->getNullFlag()){
				return null;
			}
			Debug::error("{$f} value is undefined and not null for this ".$this->getDebugString());
		}
		return $this->value;
	}
}
