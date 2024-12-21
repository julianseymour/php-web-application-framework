<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\backwards_ref_enabled;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait InputTrait{
	
	protected $input;
	
	public function releaseInput(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasInput()){
			Debug::error("{$f} input is undefined for this ".$this->getDebugString());
		}
		$input = $this->input;
		unset($this->input);
		if(!BACKWARDS_REFERENCES_ENABLED){
			return;
		}
		$this->release($input, $deallocate);
	}
	
	public function setInput(InputlikeInterface $input){
		if($this->hasInput()){
			$this->releaseInput();
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->input = $input;
		}
		return $this->input = $this->claim($input);
	}
	
	public function hasInput():bool{
		return isset($this->input);
	}
	
	public function getInput(){
		$f = __METHOD__;
		if(!$this->hasInput()){
			Debug::error("{$f} input is undefined");
		}
		return $this->input;
	}
}