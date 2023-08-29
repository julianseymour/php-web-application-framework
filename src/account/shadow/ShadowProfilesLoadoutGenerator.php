<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\shadow;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;

class ShadowProfilesLoadoutGenerator extends LoadoutGenerator{

	public function getRootNodeTreeSelectStatements(): ?array{
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

