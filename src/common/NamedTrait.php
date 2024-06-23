<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait NamedTrait{

	protected $name;

	public function setName(?string $name):?string{
		$f = __METHOD__;
		if(!is_string($name) && !$name instanceof ValueReturningCommandInterface){
			Debug::error("{$f} name must be a string or value-returning command");
		}elseif($this->hasName()){
			$this->release($this->name);
		}
		return $this->name = $this->claim($name);
	}

	public function hasName():bool{
		return isset($this->name);
	}

	public function getName():string{ // note to self: if you declare a return type of string for a function, and the function returns somerhing that has a __toString() method, the function will return its string conversion
		$f = __METHOD__;
		if(!$this->hasName()){
			Debug::error("{$f} name is undefined");
		}
		return $this->name;
	}

	public function named(?string $name){
		$this->setName($name);
		return $this;
	}
}
