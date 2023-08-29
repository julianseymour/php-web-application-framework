<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\x;
use Exception;

abstract class ValidateAnonymousConfirmationCodeUseCase extends ValidateConfirmationCodeUseCase{

	public static function requireAnonymousConfirmation(){
		$f = __METHOD__;
		try {
			return true;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function decrypt(string $data): ?string{
		return app()->acquireCurrentServerKeypair(null)->decrypt($data, LOGIN_TYPE_FULL);
	}
}
