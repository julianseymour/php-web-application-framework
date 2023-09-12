<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\timeout;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

class RefreshSessionForm extends AjaxForm
{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		$f = __METHOD__; //RefreshSessionForm::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($mode, $context);
		$this->setStyleProperty("transition", "opacity 0.5s");
		$this->setIdAttribute($this->getIdAttributeStatic());
		$this->addClassAttribute("universal_form");
		if(!$this->hasIdAttribute()) {
			Debug::error("{$f} ID attribute is undefined");
		}
	}

	public static function getMethodAttributeStatic(): ?string
	{
		return HTTP_REQUEST_METHOD_GET;
	}

	public function getDirectives(): ?array
	{
		$names = [
			DIRECTIVE_VALIDATE
		];
		if(! user() instanceof AnonymousUser) {
			array_push($names, DIRECTIVE_LOGOUT);
		}
		return $names;
	}

	public static function getIdAttributeStatic(): ?string
	{
		return "refresh_session";
	}

	public static function skipAntiXsrfTokenInputs(): bool{
		return true;
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public function generateFormHeader(): void{
		$div = new DivElement();
		$innerHTML = _("Session timeout imminent");
		$div->setInnerHTML($innerHTML);
		$this->appendChild($div);
	}

	public static function getFormDispatchIdStatic(): ?string{
		return null;
	}

	public static function getActionAttributeStatic(): ?string{
		return request()->getRequestURI();
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		$mode = $this->getAllocationMode();
		$button = new ButtonInput($mode);
		$button->setNameAttribute("directive");
		$button->setValueAttribute($name);
		$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
		$button->setTypeAttribute("submit");
		switch ($name) {
			case DIRECTIVE_VALIDATE:
				$innerHTML = _("I'm still here");
				break;
			case DIRECTIVE_LOGOUT:
				$innerHTML = _("Log out");
				break;
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
		}
		$button->setInnerHTML($innerHTML);
		return [
			$button
		];
	}

	public function getValidator(): ?Validator
	{
		$f = __METHOD__; //RefreshSessionForm::getShortClass()."(".static::getShortClass().")->getValidator()";
		$print = false;
		if($this->hasValidator()){
			return $this->validator;
		}
		$uri = request()->getRequestURIWithoutParams();
		if($print) {
			Debug::print("{$f} creating validator for URI \"{$uri}\"");
		}
		return $this->setValidator(new AntiXsrfTokenValidator($uri));
	}
}
