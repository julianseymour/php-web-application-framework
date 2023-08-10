<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticationCookie;

class PreMultifactorAuthenticationCookie extends AuthenticationCookie
{

	protected static function getReauthenticationKeyColumnName()
	{
		return "multifactorAuthenticationKey";
	}
}
