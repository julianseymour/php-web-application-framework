<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class NumberInput extends NumericInput{

	use ReadOnlyAttributeTrait;
	use RequiredAttributeTrait;

	public function getTypeAttribute(): string{
		return "number";
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_NUMBER;
	}
}
