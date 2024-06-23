<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

trait ValidateFlagTrait{
	
	use FlagBearingTrait;
	
	public function setValidateFlag(bool $value=true):bool{
		return $this->setFlag("validate", $value);
	}
	
	public function getValidateFlag():bool{
		return $this->getFlag("validate");
	}
}
