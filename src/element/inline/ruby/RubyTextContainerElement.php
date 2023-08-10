<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline\ruby;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

/**
 * this element supports only global attributes
 *
 * @author j
 *        
 */
class RubyTextContainerElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return 'rtc';
	}
}
