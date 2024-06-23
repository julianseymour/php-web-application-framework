<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

abstract class AbstractMfaResponder extends Responder
{

	protected abstract static function getLoginMfaOtpFormClass(): string;

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		$f = __METHOD__;
		$print = false;
		parent::modifyResponse($response, $use_case);
		if($print){
			Debug::print("{$f} user has MFA enabled and must now enter an OTP");
		}
		$session = new PreMultifactorAuthenticationData();
		if(!$session->hasSignature()){
			Debug::error("{$f} session signature");
		}elseif($print){
			Debug::print("{$f} about to generate login MFA form");
		}
		$otp = new InvalidatedOtp();
		$use_case = app()->getUseCase();
		$attempt = $use_case->getLoginAttempt();
		$user = $attempt->getUserData();
		$otp->setUserData($user);
		if(ULTRA_LAZY){
			$mode = ALLOCATION_MODE_ULTRA_LAZY;
		}else{
			$mode = ALLOCATION_MODE_LAZY;
		}
		$form_class = static::getLoginMfaOtpFormClass();
		$form = new $form_class($mode);
		$form->setCatchReportedSubcommandsFlag(true);
		$form->bindContext($otp);
		$command = new InfoBoxCommand($form);
		$response->pushCommand($command);
	}
}
