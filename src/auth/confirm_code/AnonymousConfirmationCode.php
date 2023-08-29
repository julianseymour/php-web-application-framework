<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

abstract class AnonymousConfirmationCode extends ConfirmationCode{

	public function getKeypair(){
		return app()->acquireCurrentServerKeypair(null);
	}

	public function getPublicKey(){
		return app()->acquireCurrentServerKeypair()->getPublicKey();
	}

	protected function encrypt(string $data): ?string{
		$f = __METHOD__;
		try {
			$keypair = app()->acquireCurrentServerKeypair(null);
			if ($keypair == null) {
				Debug::error("{$f} keypair returned null");
			}
			return $keypair->encrypt($data);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function decrypt(string $data): ?string
	{
		return app()->acquireCurrentServerKeypair(null)->decrypt($data, LOGIN_TYPE_FULL);
	}
}
