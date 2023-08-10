<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element supports only global attributes
 *
 * @author j
 *        
 */
class AbbreviationElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "abbr";
	}
}