<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

class DialogElement extends Element
{

	// XXX open attribute
	// note: you cannot use tabindex attribute on this element
	public static function getElementTagStatic(): string
	{
		return "dialog";
	}
}
