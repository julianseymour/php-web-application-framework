<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;

ErrorMessage::unimplemented(__FILE__);

class CreateGroupLoadoutGenerator extends LoadoutGenerator
{

	public function getRootNodeTreeSelectStatements(): ?array
	{
		return [
			GroupData::getPhylumName() => [
				GroupData::class => GroupData::selectStatic()->where(
					new WhereCondition("founderKey", OPERATOR_EQUALS)
				)
			]
		];
	}
}

