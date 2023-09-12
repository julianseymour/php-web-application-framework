<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\UniversalMfaResponder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class UniversalLoginUseCase extends UnresponsiveLoginUseCase{

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		switch ($status) {
			case SUCCESS:
				if(user() instanceof AnonymousUser) {
					if($print) {
						Debug::print("{$f} user is unregistered; returning parent function");
					}
					return parent::getResponder($status);
				}elseif($print) {
					Debug::print("{$f} user is registered");
				}
				return new UniversalLoginResponder();
			case RESULT_BFP_MFA_CONFIRM:
				return new UniversalMfaResponder();
			default:
				if($print) {
					Debug::print("{$f} default case");
				}
				return parent::getResponder($status);
		}
	}
}