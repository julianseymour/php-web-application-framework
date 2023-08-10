<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;

class PublicFileData extends CleartextFileData
{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::declareColumns($columns, $ds);
		$description = new TextDatum("description");
		static::pushTemporaryColumnsStatic($columns, $description);
	}
}
