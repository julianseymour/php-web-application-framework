<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\input\TextInput;

class TranslatedTextForm extends TranslatedStringForm{
	
	public static function getTextInputClass():string{
		return TextInput::class;
	}
}