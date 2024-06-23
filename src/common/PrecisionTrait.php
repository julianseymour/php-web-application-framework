<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait PrecisionTrait{
	
	protected $precision;
	
	public function setPrecision($precision){
		if($this->hasPrecision()){
			$this->release($this->precision);
		}
		return $this->precision = $this->claim($precision);
	}
	
	public function hasPrecision():bool{
		return isset($this->precision);
	}
	
	public function getPrecision(){
		$f = __METHOD__;
		if(!$this->hasPrecision()){
			Debug::error("{$f} precision is undefined");
		}
		return $this->precision;
	}
}