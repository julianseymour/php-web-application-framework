<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;

class PublicFilesLoadoutGenerator extends LoadoutGenerator
{

	public function getRootNodeTreeSelectStatements(): ?array
	{
		return [
			PublicFileData::getPhylumName() => [
				PublicFileData::class => PublicFileData::selectStatic()
			]
		];
	}
}

