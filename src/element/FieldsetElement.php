<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\FormAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\input\DisabledAttributeTrait;

class FieldsetElement extends Element
{

	use DisabledAttributeTrait;
	use FormAttributeTrait;
	use NameAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "fieldset";
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"useFormAttribute"
		]);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->form);
	}
}
