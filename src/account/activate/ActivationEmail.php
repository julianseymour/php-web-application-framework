<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;

class ActivationEmail extends ConfirmationCodeEmail{

	/**
	 *
	 * @return PreActivationConfirmationCode
	 * {@inheritdoc}
	 * @see ConfirmationCodeEmail::getSubjectData()
	 */
	public function getSubjectData(){
		return parent::getSubjectData();
	}

	public function getPlaintextBody():string{
		return substitute(_("An account has been registered at %1% for this email address. To activate your account, please visit the following URL:"), DOMAIN_PASCALCASE);
	}

	public function getSubjectLine():string{
		return DOMAIN_PASCALCASE . " -- " . _("Account confirmation required");
	}

	public function isOptional():bool{
		return false;
	}

	public static function getSubtypeStatic():string{
		return NOTIFICATION_TYPE_REGISTRATION;
	}

	protected function getDefaultActionPrompt():string{
		return _("Activate your account");
	}
}
