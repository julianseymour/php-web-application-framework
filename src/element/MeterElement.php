<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\FormAttributeTrait;

class MeterElement extends ValuedElement{

	use FormAttributeTrait;

	// XXX attributes: min, max, low, high, optimum
	public static function getElementTagStatic(): string{
		return "meter";
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
