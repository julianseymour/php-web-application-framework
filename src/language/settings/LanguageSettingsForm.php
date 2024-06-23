<?php

namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\GhostButton;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;

class LanguageSettingsForm extends AjaxForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setStyleProperty("transition", "opacity 0.5s");
		$this->addClassAttribute("universal_form");
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function bindContext($context){
		$this->setIdAttribute('language_settings_form');
		return parent::bindContext($context);
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_LANGUAGE
		];
	}

	public function getFormDataIndices(): ?array{
		return [
			"languagePreference" => GhostButton::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "language_settings";
	}

	public static function getActionAttributeStatic(): ?string{
		return request()->getRequestURI();
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__; 
		$mode = $this->getAllocationMode();
		switch($name){
			case DIRECTIVE_LANGUAGE:
				$buttons = [];
				foreach(config()->getSupportedLanguages() as $lang){
					$button = new ButtonInput($mode);
					$button->setNameAttribute("directive[{$name}]");
					$button->setValueAttribute($lang);
					$button->setInnerHTML(Internationalization::getLanguageNameFromCode($lang));
					$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
					$button->setTypeAttribute("submit");
					array_push($buttons, $button);
				}
				return $buttons;
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}
