<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

class StandaloneMfaResponder extends AbstractMfaResponder
{

	protected static function getLoginMfaOtpFormClass(): string
	{
		return StandaloneLoginMfaOtpForm::class;
	}
}
