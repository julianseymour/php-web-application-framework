<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SymbolicTrait{

	protected $symbol;

	public function setSymbol($symbol){
		if($this->hasSymbol()){
			$this->release($this->symbol);
		}
		return $this->symbol = $this->claim($symbol);
	}
	
	public function hasSymbol():bool{
		return isset($this->symbol);
	}
	
	public function getSymbol(){
		$f = __METHOD__;
		if(!$this->hasSymbol()){
			Debug::error("{$f} symbol is undefined");
		}
		return $this->symbol;
	}
}