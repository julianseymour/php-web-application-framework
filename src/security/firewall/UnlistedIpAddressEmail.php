<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeEmail;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class UnlistedIpAddressEmail extends ConfirmationCodeEmail{

	public function getPlaintextBody():string{
		$subject = $this->getSubjectData();
		return substitute(
			_("Someone attempted to access your account from unauthorized IP address %1% on a browser with user agent string \"%2%\". To authorize this IP address, please vist the following URL:"), 
			$subject->getIpAddress(), $subject->getUserAgent()
		);
	}

	public function getSubjectLine():string{
		return WEBSITE_NAME . ": " . substitute(_("Access attempted from IP address %1%"), $this->getSubjectData()->getIpAddress());
	}

	public function setSubjectData($subject){
		$f = __METHOD__;
		if(!$subject instanceof UnlistedIpAddressConfirmationCode){
			$class = $subject->getClass();
			Debug::error("{$f} subject must be an unlisted IP address confirmation code, but it is a \"{$class}\"");
		}
		return parent::setSubjectData($subject);
	}

	public function isOptional():bool{
		return true;
	}

	public static function getSubtypeStatic():string{
		return NOTIFICATION_TYPE_UNLISTED_IP;
	}

	protected function getDefaultActionPrompt():string{
		return _("Authorize or ban this IP address");
	}
}
