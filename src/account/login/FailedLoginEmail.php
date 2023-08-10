<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNotificationData;

class FailedLoginEmail extends EmailNotificationData{

	public function getSubjectLine(){
		$ip_address = $this->getSubjectData()->getInsertIpAddress();
		return substitute(_("Failed login from %1%"), $ip_address);
	}

	public function getPlaintextBody(){
		$subject = $this->getSubjectData();
		return substitute(
			_("Someone attempted and failed to access your %1% account on %2% from a device with IP address %3% and user agent string \"%4%\"."), 
			WEBSITE_NAME, 
			getDateTimeStringFromTimestamp(
				$subject->getInsertTimestamp(), 
				$this->getUserTimezone()
			), 
			$subject->getInsertIpAddress(), 
			$subject->getUserAgent()
		);
	}

	public function isOptional(){
		return true;
	}

	public static function getNotificationType(){
		return NOTIFICATION_TYPE_SECURITY;
	}

	public function getActionURIPromptMap(){
		$ip = $this->getSubjectData()->getInsertIpAddress();
		return [
			"/account_firewall?warn={$ip}" => _("Visit your account firewall")
		];
	}
}
