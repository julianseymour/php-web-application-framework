<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\embed;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element has only global attributes
 *
 * @author j
 */
class PictureElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "picture";
	}
}