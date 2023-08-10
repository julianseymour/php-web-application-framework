<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

/**
 * this element supports only global attributes
 *
 * @author j
 */
class NoScriptElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "noscript";
	}
}
