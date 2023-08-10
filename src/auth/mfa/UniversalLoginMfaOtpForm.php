<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\request;

class UniversalLoginMfaOtpForm extends AbstractLoginMfaOtpForm
{

	public static function getActionAttributeStatic(): ?string
	{
		return request()->getRequestURI();
	}
}
