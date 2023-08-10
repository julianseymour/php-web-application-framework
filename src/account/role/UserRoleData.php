<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\role;

use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;

/**
 * represents the intersection between a user and a group-associated role
 *
 * @author j
 *        
 */
class UserRoleData extends UserOwned
{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::declareColumns($columns, $ds);
		$level = new UnsignedIntegerDatum("level", 8);
		$level->setNullable(true);
		$role_key = new ForeignKeyDatum("roleKey");
		$role_key->setForeignDataStructureClass(RoleDeclaration::class);
		$role_key->setNullable(true);
		$role_key->constrain();
		$expires = new TimestampDatum("expirationTimestamp");
		static::pushTemporaryColumnsStatic($columns, $role_key, $level, $expires);
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Role");
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Roles");
	}

	public static function getTableNameStatic(): string
	{
		return "user_roles";
	}

	public static function getDataType(): string
	{
		return DATATYPE_USER_ROLE_INTERSECTION;
	}

	public static function getPhylumName(): string
	{
		return "user_roles";
	}
}
