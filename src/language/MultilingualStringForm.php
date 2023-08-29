<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\TextareaInput;
ErrorMessage::deprecated(__FILE__);

class MultilingualStringForm extends AjaxForm{

	public static function getFormDispatchIdStatic(): ?string{
		$f = __METHOD__;
		return "multilingual_string";
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getFormDataIndices(): ?array{
		$ret = [];
		foreach (config()->getSupportedLanguages() as $lang) {
			$ret[$lang] = TextareaInput::class;
		}
		return $ret;
	}

	public function getDirectives(): ?array{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getActionAttributeStatic(): ?string{
		$f = __METHOD__;
		return null;
	}

	public static function getNewFormOption(): bool{
		return true;
	}
}
