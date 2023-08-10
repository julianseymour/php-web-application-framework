<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\template;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element supports only global attributes
 *
 * @author j
 */
class TemplateElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "template";
	}
}
