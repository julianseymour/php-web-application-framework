<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

/**
 * a useless element that requires javascript to do something that could be done with a checkbox and CSS
 *
 * @author j
 *        
 */
class DetailsElement extends Element{

	// XXX open attribute
	public static function getElementTagStatic(): string{
		return "details";
	}
}
