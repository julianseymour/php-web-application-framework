<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class NonexistentUriLoadoutGenerator extends LoadoutGenerator{
	
	public function getRootNodeTreeSelectStatements(?PlayableUser $ds=null, ?UseCase $use_case=null):?array{
		return [
			NonexistentUriData::getPhylumName() => [
				NonexistentUriData::class => NonexistentUriData::selectStatic()
			]
		];
	}
}

