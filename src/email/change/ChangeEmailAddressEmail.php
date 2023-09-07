<?php

namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;

class ChangeEmailAddressEmail extends ConfirmationCodeEmail{

	public function getPlaintextBody():string{
		return substitute(
			_("This email was sent because your email adress was submitted to %1% as a new email address for an existing account. To confirm %2% as your new email address, visit the following link:"), 
			WEBSITE_NAME, 
			$this->getSubjectData()->getNewEmailAddress()
		);
	}

	public function getSubjectLine():string{
		return DOMAIN_PASCALCASE . " : " . _("Confirm email address");
	}

	public function isOptional():bool{
		return false;
	}

	public static function getSubtypeStatic():string{
		return NOTIFICATION_TYPE_CHANGE_EMAIL;
	}

	protected function getDefaultActionPrompt():string{
		return _("Change your email address");
	}
}
