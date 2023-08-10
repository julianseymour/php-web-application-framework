<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class RangeInput extends NumericInput
{

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_RANGE;
	}

	public function getTypeAttribute(): string
	{
		return "range";
	}
}
