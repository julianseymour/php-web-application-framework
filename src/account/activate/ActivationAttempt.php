<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\CodeConfirmationAttempt;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class ActivationAttempt extends CodeConfirmationAttempt{

	public static function getPhylumName(): string{
		return "attempts";
	}

	public static function getSuccessfulResultCode():int{
		return SUCCESS;
	}

	public function getName():string{
		return $this->getUserNormalizedName();
	}

	public static function getSubtypeStatic(): string{
		return ACCESS_TYPE_ACTIVATION;
	}

	public static function getConfirmationCodeClass(): string{
		return PreActivationConfirmationCode::class;
	}

	public function isSecurityNotificationWarranted():bool{
		return false;
	}

	public static function getPrettyClassName():string{
		return _("Account ativation attempt");
	}

	public static function getReasonLoggedStatic(){
		return BECAUSE_ACTIVATION;
	}

	public static function getPrettyClassNames():string{
		return _("Account activation attempts");
	}
}
