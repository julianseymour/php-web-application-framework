<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeInterface;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\input\InputTrait;

class LabelElement extends Element implements ForAttributeInterface{

	use ForAttributeTrait;
	use InputTrait;
	
	public function dispose(bool $deallocate=false): void{
		if($this->hasInput()){
			$this->releaseInput($deallocate);
		}
		parent::dispose($deallocate);
	}

	public static function getElementTagStatic(): string{
		return "label";
	}
}
