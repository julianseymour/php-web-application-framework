<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;

class ResendActivationEmailForm extends AjaxForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("resend_activation_form");
	}

	public function generateFormHeader(): void{
		$div = new DivElement($this->getAllocationMode());
		$div->setInnerHTML(_("Please activate your account"));
		$this->appendChild($div);
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_EMAIL_CONFIRMATION_CODE
		];
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "resend_activation";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/resend';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_EMAIL_CONFIRMATION_CODE:
				$button = new ButtonInput($this->getAllocationMode());
				$button->setNameAttribute("directive");
				$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
				$button->setValueAttribute($name);
				$button->setInnerHTML(_("Resend activation email"));
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
