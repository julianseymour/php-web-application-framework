<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

class ResetInput extends InputElement{

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_RESET;
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}
}
