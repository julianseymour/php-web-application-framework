<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\lockout;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\AnonymousConfirmationCode;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;

class LockoutConfirmationCode extends AnonymousConfirmationCode
{

	/**
	 *
	 * @return AuthenticatedUser
	 * {@inheritdoc}
	 * @see AnonymousconfirmationCode::getUserData()
	 */
	public function getUserData():UserData{
		return parent::getUserData();
	}

	public function setEmailAddress(string $email):string{
		return $email;
	}

	public static function getSentEmailStatus():int{
		$f = __METHOD__;
		try {
			return RESULT_BFP_USERNAME_LOCKOUT_START;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_INSERT:
				if (! app()->hasUserData()) {
					return SUCCESS;
				}
				return new AnonymousAccountTypePermission($name);
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public function isSecurityNotificationWarranted(){
		return false;
	}

	public static function getConfirmationUriStatic(string $suffix):string{
		return WEBSITE_URL . "/unlock/{$suffix}";
	}

	public static function getEmailNotificationClass():?string{
		return LockoutEmail::class;
	}

	public static function getSubtypeStatic():string{
		return ACCESS_TYPE_LOCKOUT_WAIVER;
	}

	public static function getReasonLoggedStatic():string{
		return BECAUSE_LOCKOUT;
	}
}
