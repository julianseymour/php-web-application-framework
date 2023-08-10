<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DimensionAttributesTrait;

class CanvasElement extends Element
{

	use DimensionAttributesTrait;

	public static function getElementTagStatic(): string
	{
		return "canvas";
	}
}
