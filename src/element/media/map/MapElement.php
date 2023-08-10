<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\map;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;

class MapElement extends Element
{

	use NameAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "map";
	}
}