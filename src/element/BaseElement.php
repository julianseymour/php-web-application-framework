<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\inline\HypertextAttributeTrait;

class BaseElement extends EmptyElement
{

	use HypertextAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "base";
	}
}
