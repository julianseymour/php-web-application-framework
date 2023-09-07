<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\config;
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
		foreach (config()->getSupportedLanguages() as $lang) {
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
}
