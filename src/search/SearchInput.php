<?php
namespace JulianSeymour\PHPWebApplicationFramework\search;

use JulianSeymour\PHPWebApplicationFramework\input\TextInput;

class SearchInput extends TextInput
{

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_SEARCH;
	}
}
