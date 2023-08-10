<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element supports only global attributes
 *
 * @author j
 */
class DataListElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "datalist";
	}
}
