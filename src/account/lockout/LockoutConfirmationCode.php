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

	public function setEmailAddress($email){
		return $email;
	}

	public static function getSentEmailStatus(){
		$f = __METHOD__; //LockoutConfirmationCode::getShortClass()."(".static::getShortClass().")::getSentEmailStatus()";
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

	public static function getConfirmationUriStatic($suffix){
		return WEBSITE_URL . "/unlock/{$suffix}";
	}

	public static function getEmailNotificationClass()
	{
		return LockoutEmail::class;
	}

	public static function getConfirmationCodeTypeStatic()
	{
		return ACCESS_TYPE_LOCKOUT_WAIVER;
	}

	public static function getIpLogReason()
	{
		return BECAUSE_LOCKOUT;
	}
}
