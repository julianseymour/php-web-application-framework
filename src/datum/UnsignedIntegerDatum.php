<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

class UnsignedIntegerDatum extends IntegerDatum{

	public static function validateStatic($value): int{
		return parent::validateStatic($value) === SUCCESS ? ($value >= 0 ? SUCCESS : FAILURE) : FAILURE;
	}

	public function isUnsigned():bool{
		return true;
	}

	public function setUnsigned(bool $value=true):bool{
		return $value;
	}

	public function hasMinimumValue():bool{
		return true;
	}

	public function getMinimumValue():int{
		if(!isset($this->minimumValue)){
			return 0;
		}
		return parent::getMinimumValue();
	}
	
	public function getDisableDeallocationFlag():bool{
		$f = __METHOD__;
		return parent::getDisableDeallocationFlag();
	}
}
