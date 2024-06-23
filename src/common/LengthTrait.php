<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait LengthTrait{

	protected $lengthValue;

	public function setLength($length){
		$f = __METHOD__;
		if(!is_int($length)){
			Debug::error("{$f} length must be a positive integer");
		}elseif($length < 1){
			Debug::error("{$f} length must be positive");
		}elseif($this->hasLength()){
			$this->release($this->lengthValue);
		}
		return $this->lengthValue = $this->claim($length);
	}

	public function hasLength():bool{
		return isset($this->lengthValue);
	}

	public function getLength(){
		$f = __METHOD__;
		if(!$this->hasLength()){
			Debug::error("{$f} length is undefined");
		}
		return $this->lengthValue;
	}

	public function length($length){
		$this->setLength($length);
		return $this;
	}
}
