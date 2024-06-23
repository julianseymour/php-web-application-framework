<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\settings\display_name;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;

class DisplayNameInnerForm extends AjaxForm{

	public function generateButtons(string $directive): ?array{
		$f = __METHOD__; //DisplayNameInnerForm::getShortClass()."(".static::getShortClass().")->generateButtons()";
		switch($directive){
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($directive);
				$button->setInnerHTML(_("Update display name"));
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$directive}\"");
				return null;
		}
	}

	public function getFormDataIndices(): ?array{
		return [
			"displayName" => TextInput::class
		];
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}
	
	public function reconfigureInput($input):int{
		switch($input->getColumnName()){
			case "displayName":
				$input->setStyleProperties([
					"display" => "block",
					"margin-bottom" => "1rem"
				]);
			default:
				return parent::reconfigureInput($input);
		}
	}
}
