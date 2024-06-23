<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
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
	
	public function reconfigureInput($input):int{
		$f = __METHOD__;
		$print = false;
		if(
			!$input->hasLabelString() &&
			$input->getColumnName() === "value"
		){
			$that = $this->getSuperiorForm();
			$p1 = $that->getSuperiorForm()->getContext()->getColumn($that->getSuperiorFormIndex())->getHumanReadableName();
			$p2 = $that->getContext()->getColumn($this->getSuperiorFormIndex())->getHumanReadableName();
			if($print){
				Debug::print("{$f} Pieces are \"{$p1}\" and \"{$p2}\"");
			}
			$ls = new ConcatenateCommand($p1," (", $p2, ")");
			$input->setLabelString($ls->evaluate());
			deallocate($ls);
		}
		return parent::reconfigureInput($input);
	}
}
