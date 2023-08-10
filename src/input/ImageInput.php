<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\DimensionAttributesTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\image\AlternateTextAttributeTrait;

class ImageInput extends SubmitInput
{

	use AlternateTextAttributeTrait, DimensionAttributesTrait, SourceAttributeTrait;

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_IMAGE;
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}
}
