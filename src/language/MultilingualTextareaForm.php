<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

class MultilingualTextareaForm extends MultilingualStringForm{
	
	public static function getNestedFormClass():string{
		return TranslatedTextareaForm::class;
	}
}
