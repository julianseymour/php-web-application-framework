<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;

class ActivationEmail extends ConfirmationCodeEmail
{

	/**
	 *
	 * @return PreActivationConfirmationCode
	 * {@inheritdoc}
	 * @see ConfirmationCodeEmail::getSubjectData()
	 */
	public function getSubjectData(){
		return parent::getSubjectData();
	}

	public function getPlaintextBody(){
		return substitute(_("An account has been registered at %1% for this email address. To activate your account, please visit the following URL:"), DOMAIN_PASCALCASE);
	}

	public function getSubjectLine(){
		return DOMAIN_PASCALCASE . " -- " . _("Account confirmation required");
	}

	public function isOptional(){
		return false;
	}

	public static function getNotificationType(){
		return NOTIFICATION_TYPE_REGISTRATION;
	}

	protected function getDefaultActionPrompt(){
		return _("Activate your account");
	}
}