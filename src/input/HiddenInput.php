<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class HiddenInput extends InputElement{

	public function getTypeAttribute(): string{
		return "hidden";
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_HIDDEN;
	}

	public function getAllocationMode(): int{
		return ALLOCATION_MODE_NEVER;
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}
}
