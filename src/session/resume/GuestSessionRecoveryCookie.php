<?php

namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

class GuestSessionRecoveryCookie extends SessionRecoveryCookie{

	protected static function getCookieSecretColumnName():string{
		return "guestCookieSecret";
	}

	protected static function getRecoveryKeyColumnName():string{
		return "guestRecoveryKey";
	}
}
