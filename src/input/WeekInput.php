<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class WeekInput extends ChronometricInput
{

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_WEEK;
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}
}
