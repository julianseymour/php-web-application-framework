<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\figure;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element has only global attributes
 *
 * @author j
 *        
 */
class FigureCaptionElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "figcaption";
	}
}