<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\inline\HypertextLanguageAttributeTrait;

class LinkElement extends EmptyElement
{

	use HypertextLanguageAttributeTrait;
	use TypeAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "link";
	}

	public function getUri()
	{
		return static::getUriStatic();
	}
}
