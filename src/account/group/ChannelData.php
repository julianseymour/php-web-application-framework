<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\DescriptionColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\EnabledTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;

class ChannelData extends DataStructure{

	use DescriptionColumnTrait;
	use EnabledTrait;
	use GroupKeyColumnTrait;
	use NameColumnTrait;

	public static function getDatabaseNameStatic():string{
		return "user_content";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$name = new NameDatum("name");
		$category = new TextDatum("category");
		$category->setNullable(true);
		$language = new StringEnumeratedDatum("language");
		$language->setValidEnumerationMap(config()->getSupportedLanguages());
		$language->setValue(LANGUAGE_DEFAULT);
		$group_key = new ForeignKeyDatum("groupKey");
		$group_key->setForeignDataStructureClass(GroupData::class);
		$group_key->constrain();
		$enabled = static::getIsEnabledDatum(true);
		$type = new StringEnumeratedDatum("channelType");
		$type->setNullable(false);
		static::pushTemporaryColumnsStatic($columns, $name, $category, $language, $group_key, $enabled, $type);
	}

	public static function getPrettyClassName():string{
		return _("Channel");
	}

	public static function getTableNameStatic(): string{
		return "channels";
	}

	public static function getDataType(): string{
		return DATATYPE_CHANNEL;
	}

	public static function getPrettyClassNames():string{
		return _("Channels");
	}

	public static function getPhylumName(): string{
		return "channels";
	}
}
