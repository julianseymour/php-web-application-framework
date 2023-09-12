<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\logout;

use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use Exception;

class LogoutForm extends AjaxForm{

	use JavaScriptCounterpartTrait;
	
	public static function getJavaScriptClassPath(): ?string{
		$fn = get_class_filename(static::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}
	
	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public static function skipAntiXsrfTokenInputs(): bool{
		return true;
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try{
			$inputs = parent::getAdHocInputs();
			$uri = request()->getRequestURI();
			$uri_input = new HiddenInput($this->getAllocationMode());
			$uri_input->setNameAttribute("refresh_uri");
			$uri_input->setValueAttribute($uri);
			$inputs[$uri_input->getNameAttribute()] = $uri_input;
			return $inputs;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_LOGOUT
		];
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("universal_form", "slide_menu_label", "background_color_1");
		$this->setIdAttribute("logout_form");
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "logout";
	}

	public static function getActionAttributeStatic(): ?string{
		return request()->getRequestURI();
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_LOGOUT:
				$button = new ButtonInput();
				$button->setNameAttribute("directive");
				$button->setValueAttribute($name);
				$button->setInnerHTML(_("Log out"));
				$button->setOnClickAttribute("LogoutForm.logoutButtonClicked(event, this);");
				$button->setTypeAttribute("submit");
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}
