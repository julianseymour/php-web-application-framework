<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\template;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;

class SlotElement extends Element
{

	use NameAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "slot";
	}
}
