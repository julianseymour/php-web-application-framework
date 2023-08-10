<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\UniversalLoginResponder;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class UniversalMfaUseCase extends MfaUseCase
{

	public function getResponder(): ?Responder
	{
		$f = __METHOD__;
		$print = false;
		if (user() instanceof AnonymousUser) {
			if ($print) {
				Debug::print("{$f} user is unregistered; returning parent function");
			}
			return parent::getResponder();
		}
		$status = $this->getObjectStatus();
		switch ($status) {
			case SUCCESS:
				return new UniversalLoginResponder();
			default:
				if ($print) {
					Debug::print("{$f} default case");
				}
				return parent::getResponder();
		}
	}
}

