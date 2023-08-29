<?php
namespace JulianSeymour\PHPWebApplicationFramework\contact;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\EmailInput;
use JulianSeymour\PHPWebApplicationFramework\input\TextareaInput;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptcha;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptchaValidator;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

class ContactUsForm extends AjaxForm{
	
	public function generateButtons(string $directive): ?array{
		$f = __METHOD__;
		switch($directive){
			case DIRECTIVE_SUBMIT:
				return [$this->generateGenericButton($directive)];
			default:
				Debug::error("{$f} invalid directive \"{$directive}\"");
		}
		return null;
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_SUBMIT
		];
	}

	public function getFormDataIndices(): ?array{
		return [
			"senderEmailAddress" => EmailInput::class,
			"plaintextBody" => TextareaInput::class
		];
	}

	public function getValidator():?Validator{
		if($this->hasValidator()){
			return $this->validator;
		}
		$form = new FormDataIndexValidator($this);
		$form->pushCovalidators(new hCaptchaValidator(), new AntiXsrfTokenValidator("/contact"));
		return $this->setValidator($form);
	}
	
	public function getAdHocInputs(): array{
		$inputs = parent::getAdHocInputs();
		$mode = $this->getAllocationMode();
		$hcaptcha = new hCaptcha($mode);
		$hcaptcha->setIdAttribute('contact_hcaptcha');
		$inputs['hCaptcha'] = $hcaptcha;
		return $inputs;
	}
	
	public function reconfigureInput($input):int{
		switch($input->getColumnName()){
			case "plaintextBody":
				$input->setLabelString("Enter body of your question/comment");
				$input->require();
				break;
			case "senderEmailAddress":
				$input->setLabelString("Enter your email address");
				$input->removeValueAttribute();
				$input->require();
				break;
			default:
		}
		return parent::reconfigureInput($input);
	}
	
	public static function getActionAttributeStatic():?string{
		return "/contact";
	}
	
	public static function getMethodAttributeStatic():?string{
		return HTTP_REQUEST_METHOD_POST;
	}
}

