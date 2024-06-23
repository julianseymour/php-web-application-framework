<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ValidatorTrait{

	protected $validator;

	public function setValidator(?Validator $validator):?Validator{
		if($this->hasValidator()){
			$this->release($this->validator);
		}
		return $this->validator = $this->claim($validator);;
	}
	
	public function hasValidator():bool{
		return isset($this->validator);
	}

	public function getValidator():?Validator{
		$f = __METHOD__;
		if(!$this->hasValidator()){
			Debug::error("{$f} validator is undefined");
		}
		return $this->validator;
	}
}
