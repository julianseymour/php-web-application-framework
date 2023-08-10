<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth;

use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressEmail;

class ReauthenticationEvent extends AccessAttempt
{

	protected $loginSuccessful;

	public function getLoginSuccessful()
	{
		return $this->loginSuccessful;
	}

	public static function getAccessTypeStatic(): string
	{
		return ACCESS_TYPE_REAUTHENTICATION;
	}

	/*
	 * public static function useCorrespondentAsNotificationSubjectParent(){
	 * return false;
	 * }
	 */
	public function isSecurityNotificationWarranted()
	{
		return false;
	}

	public static function getEmailNotificationClass()
	{
		return UnlistedIpAddressEmail::class;
	}

	public function setLoginSuccessful($value)
	{
		return $this->loginSuccessful = $value;
	}

	public function setLoginResult($status)
	{
		return $this->setObjectStatus($status);
	}

	public function getLoginResult()
	{
		return $this->getObjectStatus();
	}

	public static final function getPhylumName(): string
	{
		return "reauthentications";
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Reauthentication attempt");
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Reauthentication attempts");
	}

	public static function getTableNameStatic(): string
	{
		return "reauthentications";
	}

	public static function getIpLogReason()
	{
		return BECAUSE_REAUTH;
	}
}
