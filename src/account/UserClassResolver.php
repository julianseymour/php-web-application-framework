<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;

class UserClassResolver extends IntersectionTableResolver{

	public static function getIntersections():array{
		return [
			DATATYPE_USER => [
				ACCOUNT_TYPE_ADMIN => config()->getAdministratorClass(),
				ACCOUNT_TYPE_GUEST => config()->getGuestUserClass(),
				ACCOUNT_TYPE_USER => config()->getNormalUserClass(),
				ACCOUNT_TYPE_SHADOW => config()->getShadowUserClass()
			]
		];
	}
	
	public static function getSubtypability():string{
		return SUBTYPABILITY_ALL;
	}
}
