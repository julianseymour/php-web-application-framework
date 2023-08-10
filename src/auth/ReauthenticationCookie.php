<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth;

class ReauthenticationCookie extends AuthenticationCookie
{

	protected static function getReauthenticationKeyColumnName()
	{
		return "reauthenticationKey";
	}
}
