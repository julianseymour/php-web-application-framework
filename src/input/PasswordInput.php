<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class PasswordInput extends StringInput
{

	public function getTypeAttribute(): string
	{
		return "password";
	}

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_PASSWORD;
	}
}