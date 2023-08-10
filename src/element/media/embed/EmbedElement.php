<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\embed;

use JulianSeymour\PHPWebApplicationFramework\element\EmptyElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;

class EmbedElement extends EmptyElement
{

	use SourceAttributeTrait;
	use TypeAttributeTrait;

	// height, width
	public static function getElementTagStatic(): string
	{
		return "embed";
	}
}
