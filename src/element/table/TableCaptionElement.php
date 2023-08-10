<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\table;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element supports only global attributes
 *
 * @author j
 */
class TableCaptionElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "caption";
	}
}
