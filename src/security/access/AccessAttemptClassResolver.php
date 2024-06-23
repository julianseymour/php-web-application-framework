<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\access;

use JulianSeymour\PHPWebApplicationFramework\account\lockout\LockoutWaiverAttempt;
use JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeClassResolver;
use JulianSeymour\PHPWebApplicationFramework\auth\password\reset\ResetPasswordAttempt;
use JulianSeymour\PHPWebApplicationFramework\email\change\ConfirmEmailAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\condemn\CondemnedIpAddress;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\ListIpAddressAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress;

class AccessAttemptClassResolver extends ConfirmationCodeClassResolver{

	public static function getIntersections():array{
		return array_merge(parent::getIntersections(), [
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
			]
		]);
	}
	
	public static function getSubtypability():string{
		return SUBTYPABILITY_ALL;
	}
}
