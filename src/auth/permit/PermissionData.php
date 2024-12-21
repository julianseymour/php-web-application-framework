<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\StandardDataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\PriorityColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\SignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class PermissionData extends StandardDataStructure implements StaticTableNameInterface{

	use NameColumnTrait;
	use PriorityColumnTrait;
	use StaticTableNameTrait;
	
	public static function getDatabaseNameStatic():string{
		return "security";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$name = new StringEnumeratedDatum("name");
		$policy = new BooleanDatum("policy");
		$policy->setApoptoticSignal("undefined");
		$acp = new ForeignMetadataBundle("accessControlList", $ds);
		$acp->setForeignDataStructureClass(AccessControlListData::class);
		$acp->constrain();
		$priority = new SignedIntegerDatum("priority", 16);
		array_push($columns, $name, $policy, $acp, $priority);
	}

	public static function getPrettyClassName():string{
		return _("Permission");
	}

	public static function getTableNameStatic(): string{
		return "permissions";
	}

	public static function getDataType(): string{
		return DATATYPE_PERMISSION;
	}

	public static function getPrettyClassNames():string{
		return _("Permissions");
	}

	public static function getPhylumName(): string{
		return "permissions";
	}
}
