<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;

abstract class TranslatedStringForm extends AjaxForm{
	
	public abstract static function getTextInputClass():string;
	
	public function generateButtons(string $directive):?array{
		ErrorMessage::unimplemented(__METHOD__);
	}
	
	public function getFormDataIndices():?array{
		return [
			"value" => $this->getTextInputClass(),
			"multilingualStringKey" => HiddenInput::class
		];
	}
	
	public function getDirectives(): ?array{
		ErrorMessage::unimplemented(__METHOD__);
	}
	
	public static function getNewFormOption(): bool{
		return true;
	}
}
