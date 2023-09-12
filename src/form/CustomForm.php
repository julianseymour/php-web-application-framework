<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::unimplemented(__FILE__);

class CustomForm extends AjaxForm
{

	protected $formDataIndices;
	
	public function getFormDataIndices():?array{
		$f = __METHOD__; //CustomForm::getShortClass()."(".static::getShortClass().")->getFormDataIndices()";
		if(!$this->hasFormDataIndices()){
			Debug::error("{$f} form data indices are undefined");
		}
		return $this->formDataIndices;
	}
	
	public function generateButtons(string $name): ?array{
		$f = __METHOD__; //CustomForm::getShortClass()."(".static::getShortClass().")->generateButtons({)";
		ErrorMessage::unimplemented($f);
	}

	public function getDirectives(): ?array{
		$f = __METHOD__; //CustomForm::getShortClass()."(".static::getShortClass().")->getDirectives()";
		if(!$this->hasDirectives()) {
			Debug::error("{$f} button names are undefined");
		}
		return $this->directives;
	}
}
