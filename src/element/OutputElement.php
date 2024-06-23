<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeInterface;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;

class OutputElement extends Element implements ForAttributeInterface{

	use ForAttributeTrait;
	use NameAttributeTrait;

	public static function getElementTagStatic(): string{
		return "output";
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"useFormAttribute"
		]);
	}
	
	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"useFormAttribute"
		]);
	}
}
