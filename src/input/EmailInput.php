<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class EmailInput extends StringInput
{

	use ListAttributeTrait;
	use MultipleAttributeTrait;

	public function getTypeAttribute(): string
	{
		return "email";
	}

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_EMAIL;
	}
}
