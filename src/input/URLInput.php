<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class URLInput extends StringInput
{

	use ListAttributeTrait;

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_URL;
	}
}
