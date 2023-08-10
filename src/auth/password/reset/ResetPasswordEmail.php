<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;

class ResetPasswordEmail extends ConfirmationCodeEmail{

	public function getPlaintextBody(){
		return substitute(_("A password reset request for your account was submitted to %1%. To reset your password, visit the following URL:"), WEBSITE_NAME);
	}

	public function getSubjectLine(){
		return WEBSITE_NAME . "/" . _("Password reset");
	}

	public function isOptional(){
		return false;
	}

	public static function getNotificationType(){
		return NOTIFICATION_TYPE_RESET_PASSWORD;
	}

	public function getActionURIPromptMap(){
		$subject = $this->getSubjectData();
		$map = parent::getActionURIPromptMap();
		$map[$subject->getFraudReportUri()] = _("Report a fraudulent password reset attempt");
		return $map;
	}

	protected function getDefaultActionPrompt(){
		return _("Reset your password");
	}
}
