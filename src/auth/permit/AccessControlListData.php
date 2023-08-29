<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\ParentKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\PriorityColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\SignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;

class AccessControlListData extends DataStructure{

	use NameColumnTrait;
	use ParentKeyColumnTrait;
	use PriorityColumnTrait;

	public static function getDatabaseNameStatic():string{
		return "security";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$priority = new SignedIntegerDatum("priority", 16);
		$priority->setNullable(true);
		$name = new NameDatum("name");
		$permissions = new KeyListDatum("permissions");
		$permissions->setForeignDataStructureClass(PermissionData::class);
		$permissions->volatilize();
		$parent = new ForeignKeyDatum("parentKey");
		$parent->setForeignDataStructureClass(static::class);
		$parent->setNullable(true);
		static::pushTemporaryColumnsStatic($columns, $name, $parent, $priority, $permissions);
	}

	public static function getPrettyClassName():string{
		return _("Access control list");
	}

	public static function getTableNameStatic(): string{
		return "access_control_lists";
	}

	public static function getDataType(): string{
		return DATATYPE_ACCESS_CONTROL_LIST;
	}

	public static function getPrettyClassNames():string{
		return _("Access control lists");
	}

	public static function getPhylumName(): string{
		return "accessControlLists";
	}
}
