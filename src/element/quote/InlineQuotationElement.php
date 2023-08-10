<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\quote;

use JulianSeymour\PHPWebApplicationFramework\element\CitationalElement;

class InlineQuotationElement extends CitationalElement
{

	public static function getElementTagStatic(): string
	{
		return "q";
	}
}
