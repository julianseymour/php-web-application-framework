<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\quote;

use JulianSeymour\PHPWebApplicationFramework\element\CitationalElement;

class BlockQuotationElement extends CitationalElement
{

	public static function getElementTagStatic(): string
	{
		return "blockquote";
	}
}
