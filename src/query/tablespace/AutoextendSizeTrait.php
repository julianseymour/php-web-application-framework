<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait AutoextendSizeTrait{

	protected $autoextendSizeValue;

	public function setAutoextendSize($value){
		$f = __METHOD__;
		if(!is_int($value)){
			Debug::error("{$f} this function accepts integers only");
		}elseif($this->hasAutoextendSize()){
			$this->release($this->autoextendSizeValue);
		}
		return $this->autoextendSizeValue = $this->claim($value);
	}

	public function hasAutoextendSize():bool{
		return $this->autoextendSizeValue;
	}

	public function getAutoextendSize(){
		$f = __METHOD__;
		if(!$this->hasAutoextendSize()){
			Debug::error("{$f} autoextend size is undefined");
		}
		return $this->autoextendSizeValue;
	}

	public function autoextendSize($value){
		$this->setAutoextendSize($value);
		return $this;
	}
}