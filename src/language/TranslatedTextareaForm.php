<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\input\TextareaInput;

class TranslatedTextareaForm extends TranslatedStringForm{
	
	public static function getTextInputClass():string{
		return TextareaInput::class;
	}
}
