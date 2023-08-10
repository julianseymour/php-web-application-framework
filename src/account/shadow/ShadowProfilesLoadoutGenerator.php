<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\shadow;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;

class ShadowProfilesLoadoutGenerator extends LoadoutGenerator{

	public function getRootNodeTreeSelectStatements(): ?array{
		$class = config()->getShadowUserClass();
		return [
			'users' => [
				$class => $class::selectStatic()->orderBy("lastName")
			]
		];
	}
}

