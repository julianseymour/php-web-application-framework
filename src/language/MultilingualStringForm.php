<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

abstract class MultilingualStringForm extends AjaxForm{

	public abstract static function getNestedFormClass():string;
	
	public static function getFormDispatchIdStatic(): ?string{
		return "multilingual_string";
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function generateButtons(string $name): ?array{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function getFormDataIndices(): ?array{
		$ret = [];
		foreach(config()->getSupportedLanguages() as $lang){
			$ret[$lang] = $this->getNestedFormClass();;
		}
		return $ret;
	}

	public function getDirectives(): ?array{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public static function getActionAttributeStatic(): ?string{
		return null;
	}

	public static function getNewFormOption(): bool{
		return true;
	}
	
	/*public function reconfigureInput($input):int{
		$f = __METHOD__;
		$tic = $this->getNestedFormClass()::getTextInputClass();
		if(
			!$input->hasLabelString() && 
			//$this->hasColumnName() && 
			//$input->hasSuperiorFormIndex() && 
			is_a($input, $tic)
		){
			$p1 = $this->getSuperiorForm()->getContext()->getColumn($this->getSuperiorFormIndex())->getHumanReadableName();
			$p2 = $this->getContext()->getColumn($input->getForm()->getSuperiorFormIndex())->getHumanReadableName();
			Debug::print("{$f} TextInput class is \"{$tic}\". Pieces are \"{$p1}\" and \"{$p2}\"");
			$ls = new ConcatenateCommand($p1," (", $p2, ")");
			$input->setLabelString($ls->evaluate());
			deallocate($ls);
		}
		return parent::reconfigureInput($input);
	}*/
}
