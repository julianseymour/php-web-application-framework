<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\description;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element has only global attributes
 *
 * @author j
 *        
 */
class DescriptionListElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "dl";
	}
}
