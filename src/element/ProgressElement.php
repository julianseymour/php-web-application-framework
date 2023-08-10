<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

class ProgressElement extends ValuedElement
{

	// XXX max attribute (but not min)
	public static function getElementTagStatic(): string
	{
		return "progress";
	}
}
