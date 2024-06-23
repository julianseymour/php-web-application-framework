<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class RadioButtonInput extends CheckedInput{

	public function getSensitiveFlag(): bool{
		return false;
	}

	public function hasIdAttribute(): bool{
		return true;
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_RADIO;
	}
}
