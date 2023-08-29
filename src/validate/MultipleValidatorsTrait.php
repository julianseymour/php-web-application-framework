<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultipleValidatorsTrait{

	use ArrayPropertyTrait;

	public function hasValidators(){
		return $this->hasArrayProperty("validators");
	}

	public function getValidators(){
		return $this->getProperty("validators");
	}

	public function pushValidator(...$validators){
		foreach ($validators as $validator) {
			$validator->setInput($this);
		}
		return $this->pushArrayProperty("validators", ...$validators);
	}

	public function getValidatorCount(){
		return $this->getArrayPropertyCount("validators");
	}
}