<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\figure;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element has only global attributes
 *
 * @author j
 *        
 */
class FigureElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "figure";
	}
}