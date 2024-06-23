<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\account\role\RoleDeclaration;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;

class GrantSubjectClassResolver extends IntersectionTableResolver{

	public static function getIntersections(): array{
		return [
			DATATYPE_ROLE => RoleDeclaration::class,
			// DATATYPE_IP_ADDRESS => StoredIpAddress::class,
			DATATYPE_USER => mods()->getUserClasses()
		];
	}
	
	public static function getSubtypability():string{
		return SUBTYPABILITY_PARTIAL;
	}
}
