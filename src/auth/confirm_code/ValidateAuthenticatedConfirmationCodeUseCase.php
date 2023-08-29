<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;
use mysqli;

abstract class ValidateAuthenticatedConfirmationCodeUseCase extends ValidateConfirmationCodeUseCase{

	public static function requireAnonymousConfirmation(){
		return false;
	}

	protected function decrypt(string $data): ?string{
		return user()->decrypt($data);
	}
}
