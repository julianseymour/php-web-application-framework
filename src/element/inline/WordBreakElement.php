<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\element\EmptyElement;

/**
 * this element has only standard attributes
 *
 * @author j
 */
class WordBreakElement extends EmptyElement
{

	public static function getElementTagStatic(): string
	{
		return "wbr";
	}
}
