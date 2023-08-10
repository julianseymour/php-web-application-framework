<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;

class ChangeEmailAddressEmail extends ConfirmationCodeEmail{

	public function getPlaintextBody(){
		return substitute(
			_("This email was sent because your email adress was submitted to %1% as a new email address for an existing account. To confirm %2% as your new email address, visit the following link:"), 
			WEBSITE_NAME, 
			$this->getSubjectData()->getNewEmailAddress()
		);
	}

	public function getSubjectLine(){
		return DOMAIN_PASCALCASE . " :: " . _("Confirm email address");
	}

	public function isOptional(){
		return false;
	}

	public static function getNotificationType(){
		return NOTIFICATION_TYPE_CHANGE_EMAIL;
	}

	protected function getDefaultActionPrompt(){
		return _("Change your email address");
	}
}
