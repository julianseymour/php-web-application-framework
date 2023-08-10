<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class TimeInput extends ChronometricInput
{

	public function getTypeAttribute(): string
	{
		return "time";
	}

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_TIME;
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}
}