<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class PhoneNumberInput extends StringInput{

	use ListAttributeTrait;

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_TEL;
	}

	public function getTypeAttribute(): string{
		return "tel";
	}
}
