<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

class StandaloneLoginMfaOtpForm extends AbstractLoginMfaOtpForm
{

	public static function getActionAttributeStatic(): ?string
	{
		return "/mfa";
	}
}
