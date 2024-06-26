<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\CodeConfirmationAttempt;

class ListIpAddressAttempt extends CodeConfirmationAttempt{

	public static function getPhylumName(): string{
		return "listIpAttempts";
	}

	public static function getConfirmationCodeClass(): string{
		return UnlistedIpAddressConfirmationCode::class;
	}

	public static function getSuccessfulResultCode():int{
		return RESULT_CODE_VALIDATED;
	}

	public function isSecurityNotificationWarranted():bool{
		return false;
	}

	public static function getPrettyClassName():string{
		return _("IP address authorization attempt");
	}

	public static function getPrettyClassNames():string{
		return _("IP address authorization attempts");
	}

	public static function getReasonLoggedStatic(){
		return BECAUSE_IPAUTH;
	}

	public static function getSubtypeStatic(): string{
		return ACCESS_TYPE_UNLISTED_IP_ADDRESS;
	}
}
