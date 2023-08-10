<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\embed;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;

class PortalElement extends Element
{

	use SourceAttributeTrait;

	// referrerpolicy attribute
	public static function getElementTagStatic(): string
	{
		return "portal";
	}
}
