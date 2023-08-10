<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\FormAttributeTrait;

class MeterElement extends ValuedElement
{

	use FormAttributeTrait;

	// XXX attributes: min, max, low, high, optimum
	public static function getElementTagStatic(): string
	{
		return "meter";
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"useFormAttribute"
		]);
	}
}
