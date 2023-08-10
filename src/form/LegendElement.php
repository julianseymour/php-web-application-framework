<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element supports only global attributes
 *
 * @author j
 */
class LegendElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "legend";
	}
}
