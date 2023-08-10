<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\PriorityColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\SignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;

class PermissionData extends DataStructure{

	use NameColumnTrait;
	use PriorityColumnTrait;

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$name = new StringEnumeratedDatum("name");
		$policy = new BooleanDatum("policy");
		$policy->setApoptoticSignal("undefined");
		$acp = new ForeignMetadataBundle("accessControlList", $ds);
		$acp->setForeignDataStructureClass(AccessControlListData::class);
		$acp->constrain();
		$priority = new SignedIntegerDatum("priority", 16);
		static::pushTemporaryColumnsStatic($columns, $name, $policy, $acp, $priority);
	}

	public static function getPrettyClassName(?string $lang = null){
		return _("Permission");
	}

	public static function getTableNameStatic(): string{
		return "permissions";
	}

	public static function getDataType(): string{
		return DATATYPE_PERMISSION;
	}

	public static function getPrettyClassNames(?string $lang = null){
		return _("Permissions");
	}

	public static function getPhylumName(): string{
		return "permissions";
	}
}
