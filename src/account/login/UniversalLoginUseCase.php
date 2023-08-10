<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\UniversalMfaResponder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class UniversalLoginUseCase extends UnresponsiveLoginUseCase
{

	public function getResponder(): ?Responder
	{
		$f = __METHOD__; //UniversalLoginUseCase::getShortClass()."(".static::getShortClass().")->getResponder()";
		$print = false;
		$status = $this->getObjectStatus();
		switch ($status) {
			case SUCCESS:
				if (user() instanceof AnonymousUser) {
					if ($print) {
						Debug::print("{$f} user is unregistered; returning parent function");
					}
					return parent::getResponder();
				} elseif ($print) {
					Debug::print("{$f} user is registered");
				}
				return new UniversalLoginResponder();
			case RESULT_BFP_MFA_CONFIRM:
				return new UniversalMfaResponder();
			default:
				if ($print) {
					Debug::print("{$f} default case");
				}
				return parent::getResponder();
		}
	}
}