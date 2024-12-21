<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class TextareaInput extends KeypadInput{

	use ReadOnlyAttributeTrait;
	use RequiredAttributeTrait;

	public static function isEmptyElement(): bool{
		return false;
	}

	public function getTypeAttribute(): string{
		return INPUT_TYPE_TEXTAREA;
	}

	public function echoInnerHTML(bool $destroy = false): void{
		echo $this->getValueAttribute();
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_TEXTAREA;
	}

	public static function getElementTagStatic(): string{
		return INPUT_TYPE_TEXTAREA;
	}
}
