<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

class ColorInput extends InputElement{

	use ListAttributeTrait;

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_COLOR;
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}

	public function configure(?AjaxForm $form=null): int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::configure($form);
		if($this->hasLabelString()){
			if($print){
				Debug::print("{$f} pushing predecessor");
			}
			$span = new SpanElement($this->getAllocationMode());
			$this->pushPredecessor($span->withInnerHTML($this->getLabelString()));
		}elseif($print){
			Debug::print("{$f} label string is undefined");
		}
		return $ret;
	}
}
