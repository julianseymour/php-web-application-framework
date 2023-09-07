<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\role;

use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

/**
 * represents the intersection between a user and a group-associated role
 *
 * @author j
 *        
 */
class UserRoleData extends UserOwned implements StaticTableNameInterface{

	use StaticTableNameTrait;
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$level = new UnsignedIntegerDatum("level", 8);
		$level->setNullable(true);
		$role_key = new ForeignKeyDatum("roleKey");
		$role_key->setForeignDataStructureClass(RoleDeclaration::class);
		$role_key->setNullable(true);
		$role_key->constrain();
		$expires = new TimestampDatum("expirationTimestamp");
		array_push($columns, $role_key, $level, $expires);
	}

	public static function getPrettyClassName():string{
		return _("Role");
	}

	public static function getPrettyClassNames():string{
		return _("Roles");
	}

	public static function getTableNameStatic(): string{
		return "user_roles";
	}

	public static function getDataType(): string{
		return DATATYPE_USER_ROLE_INTERSECTION;
	}

	public static function getPhylumName(): string{
		return "user_roles";
	}
}
