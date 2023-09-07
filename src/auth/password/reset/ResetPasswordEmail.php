<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;

class ResetPasswordEmail extends ConfirmationCodeEmail{

	public function getPlaintextBody():string{
		return substitute(_("A password reset request for your account was submitted to %1%. To reset your password, visit the following URL:"), WEBSITE_NAME);
	}

	public function getSubjectLine():string{
		return WEBSITE_NAME . ":" . _("Password reset");
	}

	public function isOptional():bool{
		return false;
	}

	public static function getSubtypeStatic():string{
		return NOTIFICATION_TYPE_RESET_PASSWORD;
	}

	public function getActionURIPromptMap():?array{
		$subject = $this->getSubjectData();
		$map = parent::getActionURIPromptMap();
		$map[$subject->getFraudReportUri()] = _("Report a fraudulent password reset attempt");
		return $map;
	}

	protected function getDefaultActionPrompt():string{
		return _("Reset your password");
	}
}
