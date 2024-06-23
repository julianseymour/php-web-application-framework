<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\InputlikeInterface;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use Exception;

class RegistrationUsernameForm extends AjaxForm{

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$vn = $input->getColumnName();
			switch($vn){
				case "name":
					$input->setLabelString(_("Username"));
					$ret = parent::reconfigureInput($input);
					$id = "reg_name";
					$input->setIdAttribute($id);
					$input->setAutocompleteAttribute("off");
					$suffix = new DivElement();
					$suffix->addClassAttribute("js_valid_light");
					$suffix->setIdAttribute("js_valid_name");
					$suffix->setAllowEmptyInnerHTML(true);
					$input->pushSuccessor($suffix);
					$input->setRequiredAttribute("required");
					$input->getWrapperElement()->setStyleProperties([
						"margin-bottom" => "0.5rem"
					]);
					return $ret;
				default:
			}
			return parent::reconfigureInput($input);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function attachInputValidators(InputlikeInterface $input): InputlikeInterface{
		$f = __METHOD__;
		$print = false;
		if(!$input->hasColumnName()){
			return $input;
		}
		$vn = $input->getColumnName();
		if($print){
			Debug::print("{$f} variable name is \"{$vn}\"");
		}
		switch($vn){
			case "name":
				if($input->hasValidators()){
					Debug::error("{$f} registration username input already has a validator");
				}
				$input->pushValidator(new RegistrationUsernameValidator());
				break;
			default:
				break;
		}
		return $input;
	}

	public function getFormDataIndices(): ?array{
		return [
			"name" => TextInput::class
		];
	}

	public function generateButtons(string $directive): ?array{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getDirectives(): ?array{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getNewFormOption(): bool{
		return true;
	}
}
