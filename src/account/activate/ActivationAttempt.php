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

	public function getParentKey(){
		return $this->getUserKey();
	}

	public static function getSuccessfulResultCode(){
		return RESULT_ACTIVATE_SUCCESS;
	}

	public function getName(){
		return $this->getUserNormalizedName();
	}

	public static function getAccessTypeStatic(): string{
		return ACCESS_TYPE_ACTIVATION;
	}

	public static function getConfirmationCodeClass(): string{
		return PreActivationConfirmationCode::class;
	}

	protected function checkUserMFAEnabled($user, $mysqli){
		$f = __METHOD__;
		try {
			if ($user == null) {
				Debug::error("{$f} user data returned null");
				return $this->setLoginResult(ERROR_NULL_USER_OBJECT);
			} elseif ($user instanceof AnonymousUser) {
				Debug::error("{$f} user is anonymous");
				return $this->setObjectStatus(ERROR_INTERNAL);
			} elseif ($user->getMFAStatus() == MFA_STATUS_DISABLED) {
				Debug::print("{$f} login successful; user does not have multifactor authentication enabled");
				return $this->setLoginResult(SUCCESS);
			}
			return $this->setLoginResult(RESULT_ACTIVATE_SUCCESS_NOLOGIN);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isSecurityNotificationWarranted(){
		return false;
	}

	public static function getPrettyClassName(?string $lang = null){
		return _("Account ativation attempt");
	}

	public static function getIpLogReason(){
		return BECAUSE_ACTIVATION;
	}

	public static function getPrettyClassNames(?string $lang = null){
		return _("Account activation attempts");
	}
}
