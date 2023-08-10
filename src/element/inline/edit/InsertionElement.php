<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline\edit;

use JulianSeymour\PHPWebApplicationFramework\element\CitationalElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DateTimeAttributeTrait;

class InsertionElement extends CitationalElement
{

	use DateTimeAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "ins";
	}
}
