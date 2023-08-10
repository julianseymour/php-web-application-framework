<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\CodeConfirmationAttempt;

class ListIpAddressAttempt extends CodeConfirmationAttempt
{

	public static function getConfirmationCodeAlreadyUsedStatus()
	{
		return ERROR_LINK_EXPIRED;
	}

	public static function getPhylumName(): string
	{
		return "listIpAttempts";
	}

	public static function getConfirmationCodeClass(): string
	{
		return UnlistedIpAddressConfirmationCode::class;
	}

	public static function getSuccessfulResultCode()
	{
		return RESULT_CODE_VALIDATED;
	}

	public function isSecurityNotificationWarranted()
	{
		return false;
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("IP address authorization attempt");
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("IP address authorization attempts");
	}

	/*
	 * public static function getTableNameStatic():string{
	 * return "ip_authorization";
	 * }
	 */
	public static function getIpLogReason()
	{
		return BECAUSE_IPAUTH;
	}

	public static function getAccessTypeStatic(): string
	{
		return ACCESS_TYPE_UNLISTED_IP_ADDRESS;
	}
}
