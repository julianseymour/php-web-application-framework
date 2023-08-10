<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element supports only global attributes
 *
 * @author j
 *        
 */
class KeyboardElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "kbd";
	}
}
