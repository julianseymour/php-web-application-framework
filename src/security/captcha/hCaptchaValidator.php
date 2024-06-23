<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\captcha;

use function JulianSeymour\PHPWebApplicationFramework\app;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

class hCaptchaValidator extends Validator
{

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //hCaptchaValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		if(app()->getFlag("debug")){
			return SUCCESS;
		}
		$hcaptcha = hCaptcha::verifyResponse(app()->getUseCase());
		if($hcaptcha !== SUCCESS){
			$err = ErrorMessage::getResultMessage($hcaptcha);
			Debug::warning("{$f} hcaptcha validation failed with error status \"{$err}\"");
		}
		return $hcaptcha;
	}
}
