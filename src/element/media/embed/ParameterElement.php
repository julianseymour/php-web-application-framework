<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\embed;

use JulianSeymour\PHPWebApplicationFramework\element\EmptyElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ValueAttributeTrait;

class ParameterElement extends EmptyElement
{

	use NameAttributeTrait;
	use ValueAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "param";
	}
}
