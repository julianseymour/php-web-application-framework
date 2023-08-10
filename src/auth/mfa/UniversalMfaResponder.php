<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

class UniversalMfaResponder extends AbstractMfaResponder
{

	protected static function getLoginMfaOtpFormClass(): string
	{
		return UniversalLoginMfaOtpForm::class;
	}
}

