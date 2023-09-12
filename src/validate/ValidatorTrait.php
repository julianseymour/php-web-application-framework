<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ValidatorTrait{

	protected $validator;

	public function setValidator(?Validator $validator):?Validator{
		if($validator === null) {
			unset($this->validator);
			return null;
		}
		return $this->validator = $validator;
	}

	public function hasValidator():bool{
		return isset($this->validator) && $this->validator instanceof Validator;
	}

	public function getValidator():?Validator{
		$f = __METHOD__; //"ValidatorTrait(".static::getShortClass().")->getValidator()";
		if(!$this->hasValidator()) {
			Debug::error("{$f} validator is undefined");
		}
		return $this->validator;
	}
}
