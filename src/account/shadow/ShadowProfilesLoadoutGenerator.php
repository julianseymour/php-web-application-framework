<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\shadow;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ShadowProfilesLoadoutGenerator extends LoadoutGenerator{

	public function getRootNodeTreeSelectStatements(?PlayableUser $ds = null, ?UseCase $use_case = null):?array{
		$class = config()->getShadowUserClass();
		return [
			'users' => [
				$class => $class::selectStatic()->orderBy(
					new OrderByClause("lastName"), 
					new OrderByClause("firstName")
				)
			]
		];
	}
}

