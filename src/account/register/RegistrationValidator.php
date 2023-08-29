<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptchaValidator;
use JulianSeymour\PHPWebApplicationFramework\security\honeypot\HoneypotValidator;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\CheckboxValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\UniqueValidator;

class RegistrationValidator extends FormDataIndexValidator{

	public function __construct($use_case){
		parent::__construct(new RegistrationForm(ALLOCATION_MODE_FORM, new RegisteringUser()));
		// 1. anti-XSRF token validator
		$xsrf = new AntiXsrfTokenValidator('/register');
		// 2. honeypot validator
		$honey = new HoneypotValidator(RegistrationForm::class);
		// 3. hCaptcha validator
		$captcha = new hCaptchaValidator();
		// 4. agree terms checkbox validator
		$tos = new CheckboxValidator("agree_tos");
		$tos->setSpecialFailureStatus(ERROR_AGREE_TOS);
		// 5. form data indices validator ($this)
		$this->pushCovalidators($xsrf, $honey, $captcha, $tos);
		$this->setCovalidateWhen(COVALIDATE_BEFORE);
	}

	public static function getUsernameAvailableValidator($searchname){
		return new UniqueValidator(NormalUser::class, [
			"normalizedName"
		], 's', [
			$searchname
		]);
	}
}
