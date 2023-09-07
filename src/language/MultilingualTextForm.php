<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

class MultilingualTextForm extends MultilingualStringForm{

	public static function getNestedFormClass():string{
		return TranslatedTextForm::class;
	}
}
