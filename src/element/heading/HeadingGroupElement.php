<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\heading;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element has only global attributes
 *
 * @author j
 */
class HeadingGroupElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "hgroup";
	}
}
