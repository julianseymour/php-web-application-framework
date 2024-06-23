<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class MonthInput extends ChronometricInput{

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_MONTH;
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}
}
