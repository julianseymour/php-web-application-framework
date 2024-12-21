<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

/**
 * A non-interactive element that gets processed like an input by AjaxForms
 * @author j
 *
 */
class FakeInput extends InputElement{
	
	public static function isEmptyElement(): bool{
		return false;
	}
	
	public function getAllowEmptyInnerHTML():bool{
		return true;
	}
}
