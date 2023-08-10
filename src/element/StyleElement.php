<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\MediaAttributeTrait;

class StyleElement extends IntangibleElement
{

	use MediaAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "style";
	}

	/*
	 * public function getInnerHTML/ConversionMode(){
	 * return ELEMENT_INNERHTML_/CONVERSION_STRING;
	 * }
	 */
}
