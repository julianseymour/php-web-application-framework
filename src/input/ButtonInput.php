<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class ButtonInput extends ButtonlikeInput{
	
	public static function getElementTagStatic(): string{
		return "button";
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_BUTTON;
	}

	public static function isEmptyElement(): bool{
		return false;
	}
}
