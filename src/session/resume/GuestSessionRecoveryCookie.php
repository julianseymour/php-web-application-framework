<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

class GuestSessionRecoveryCookie extends SessionRecoveryCookie
{

	protected static function getCookieSecretColumnName()
	{
		return "guestCookieSecret";
	}

	protected static function getRecoveryKeyColumnName()
	{
		return "guestRecoveryKey";
	}
}
