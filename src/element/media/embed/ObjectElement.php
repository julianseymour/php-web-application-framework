<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\embed;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DimensionAttributesTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\FormAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;

class ObjectElement extends Element
{

	use DimensionAttributesTrait;
	use FormAttributeTrait;
	use NameAttributeTrait;
	use TypeAttributeTrait;

	// XXX data, typemustmatch, usemap
	public static function getElementTagStatic(): string
	{
		return "object";
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"useFormAttribute"
		]);
	}
}
