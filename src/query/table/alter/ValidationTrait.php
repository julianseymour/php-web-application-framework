<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

use function JulianSeymour\PHPWebApplicationFramework\release;

trait ValidationTrait{

	protected $validate;

	public function setValidation($validate){
		if(!is_bool($validate)){
			$validate = boolval($validate);
		}
		if($this->hasValidation()){
			$this->release($this->validate);
		}
		return $this->validate = $this->claim($validate);
	}

	public function hasValidation():bool{
		return isset($this->validate);
	}
	
	public function getValidation(){
		return $this->validate;
	}
}
