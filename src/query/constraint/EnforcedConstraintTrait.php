<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;

trait EnforcedConstraintTrait{

	protected $enforcement;

	public function setEnforcement($value){
		if(!is_bool($value)){
			$value = boolval($value);
		}elseif($this->hasEnforcement()){
			$this->release($this->enforcement);
		}
		return $this->enforcement = $this->claim($value);
	}

	public function hasEnforcement():bool{
		return $this->enforcement === true || $this->enforcement === false;
	}

	public function getEnforcement():bool{
		if(!$this->hasEnforcement()){
			return true;
		}
		return $this->enforcement;
	}
}
