<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\lockout;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;

class LockoutEmail extends ConfirmationCodeEmail{

	public function getPlaintextBody():string{
		return substitute(_("Multiple failed attempts were made to access your %1% account using invalid credentials. To protect your account it has been temporarily locked out of authorizing logins from unapproved IP addresses; you can bypass this lockout by visiting the following link from the device with which you wish to access the site:"), WEBSITE_NAME);
	}

	public function getSubjectLine():string{
		return DOMAIN_PASCALCASE . ": " . _("Login failure");
	}

	public function isOptional():bool{
		return true;
	}

	public static function getSubtypeStatic():string{
		return NOTIFICATION_TYPE_LOCKOUT;
	}

	protected function getDefaultActionPrompt():string{
		return _("Unlock your account");
	}
}
