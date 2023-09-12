<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\CodeConfirmationAttempt;
use Exception;

class ResetPasswordAttempt extends CodeConfirmationAttempt{
	
	public function getReasonLoggedString():string{
		$f = __METHOD__;
		try{
			if($this->wasLoginSuccessful()) {
				return _("Password reset");
			}
			return _("Failed reset password attempt");
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_INSERT:
				return new AnonymousAccountTypePermission($name);
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public function getReasonLogged(){
		return BECAUSE_RESET;
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try{
			$name = $this->getUserData()->getNormalizedName();
			$this->setUserName($name);
			return parent::afterGenerateInitialValuesHook();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getSuccessfulResultCode():int{
		return SUCCESS;
	}

	public static function getPhylumName(): string{
		return "passwordResetAttempts";
	}

	public static function getSubtypeStatic(): string{
		return ACCESS_TYPE_RESET;
	}

	public static function getConfirmationCodeClass(): string{
		return ResetPasswordConfirmationCode::class;
	}

	public function isSecurityNotificationWarranted(){
		return true;
	}

	public static function getPrettyClassName():string{
		return _("Password reset attempt");
	}

	public static function getPrettyClassNames():string{
		return _("Password reset attempts");
	}

	public static function getReasonLoggedStatic(){
		return BECAUSE_RESET;
	}
}
