<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ValueAttributeTrait;

class DataElement extends Element
{

	use ValueAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "data";
	}
}
