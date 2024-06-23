<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\FormAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\input\DisabledAttributeTrait;
use function JulianSeymour\PHPWebApplicationFramework\release;

class FieldsetElement extends Element{

	use DisabledAttributeTrait;
	use FormAttributeTrait;
	use NameAttributeTrait;

	public static function getElementTagStatic(): string{
		return "fieldset";
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
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasForm()){
			$this->releaseForm($deallocate);
		}
		parent::dispose($deallocate);
	}
}
