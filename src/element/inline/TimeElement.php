<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DateTimeAttributeTrait;

class TimeElement extends Element
{

	use DateTimeAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return 'time';
	}
}
