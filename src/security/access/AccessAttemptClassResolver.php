<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\access;

use JulianSeymour\PHPWebApplicationFramework\account\activate\PreActivationConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\account\lockout\LockoutConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\account\lockout\LockoutWaiverAttempt;
use JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt;
use JulianSeymour\PHPWebApplicationFramework\auth\ReauthenticationEvent;
use JulianSeymour\PHPWebApplicationFramework\auth\password\reset\ResetPasswordAttempt;
use JulianSeymour\PHPWebApplicationFramework\auth\password\reset\ResetPasswordConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;
use JulianSeymour\PHPWebApplicationFramework\email\change\ChangeEmailAddressConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\email\change\ConfirmEmailAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\condemn\CondemnedIpAddress;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\ListIpAddressAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressConfirmationCode;

class AccessAttemptClassResolver extends IntersectionTableResolver
{

	public static function getIntersections()
	{
		return [
			DATATYPE_IP_ADDRESS => [
				IP_ADDRESS_TYPE_CONDEMNED => CondemnedIpAddress::class,
				IP_ADDRESS_TYPE_LISTED => ListedIpAddress::class
			],
			DATATYPE_ACCESS_ATTEMPT => [
				ACCESS_TYPE_LOGIN => LoginAttempt::class,
				ACCESS_TYPE_RESET => ResetPasswordAttempt::class,
				ACCESS_TYPE_LOCKOUT_WAIVER => LockoutWaiverAttempt::class,
				ACCESS_TYPE_UNLISTED_IP_ADDRESS => ListIpAddressAttempt::class,
				ACCESS_TYPE_CHANGE_EMAIL => ConfirmEmailAttempt::class
			],
			DATATYPE_CONFIRMATION_CODE => [
				ACCESS_TYPE_RESET => ResetPasswordConfirmationCode::class,
				ACCESS_TYPE_LOCKOUT_WAIVER => LockoutConfirmationCode::class,
				ACCESS_TYPE_ACTIVATION => PreActivationConfirmationCode::class,
				ACCESS_TYPE_UNLISTED_IP_ADDRESS => UnlistedIpAddressConfirmationCode::class,
				ACCESS_TYPE_CHANGE_EMAIL => ChangeEmailAddressConfirmationCode::class,
				ACCESS_TYPE_REAUTHENTICATION => ReauthenticationEvent::class
			]
		];
	}
}
