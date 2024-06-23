<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use JulianSeymour\PHPWebApplicationFramework\account\group\ChannelData;
use JulianSeymour\PHPWebApplicationFramework\account\group\GroupData;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;

class GrantScopeClassResolver extends IntersectionTableResolver{

	public static function getIntersections(): array{
		return [
			DATATYPE_GROUP => GroupData::class,
			DATATYPE_CHANNEL => ChannelData::class
		];
	}
	
	public static function getSubtypability():string{
		return SUBTYPABILITY_NONE;
	}
}
