<?php

namespace JulianSeymour\PHPWebApplicationFramework\contact;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptcha;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptchaValidator;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

abstract class AbstractContactForm extends AjaxForm{
	
	public function __construct(int $mode=ALLOCATION_MODE_UNDEFINED, $context=null){
		parent::__construct($mode, $context);
		$this->setStyleProperties([
			"width" => "640px",
			"max-width" => "calc(100% - 2rem)",
			"margin" => "1.5rem auto"
		]);
	}
	
	public function generateButtons(string $directive): ?array{
		$f = __METHOD__;
		switch($directive){
			case DIRECTIVE_VALIDATE:
				return [$this->generateGenericButton($directive)];
			default:
				Debug::error("{$f} invalid directive \"{$directive}\"");
		}
		return null;
	}
	
	public function getDirectives(): ?array{
		return [
			DIRECTIVE_VALIDATE
		];
	}
	
	public function getValidator():?Validator{
		if($this->hasValidator()){
			return $this->validator;
		}
		$form = new FormDataIndexValidator($this);
		$form->pushCovalidators(
			new hCaptchaValidator(),
			new AntiXsrfTokenValidator($this->getActionAttributeStatic())
		);
		return $this->setValidator($form);
	}
	
	public function getAdHocInputs(): array{
		$inputs = parent::getAdHocInputs();
		$mode = $this->getAllocationMode();
		$hcaptcha = new hCaptcha($mode);
		$hcaptcha->setIdAttribute(new ConcatenateCommand($this->getIdAttribute(), "_captcha"));
		$inputs['hCaptcha'] = $hcaptcha;
		return $inputs;
	}
	
	public static function getMethodAttributeStatic():?string{
		return HTTP_REQUEST_METHOD_POST;
	}
}
