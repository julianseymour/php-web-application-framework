<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\UserMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\account\role\RoleDeclaration;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\KeypairColumnsTrait;
use JulianSeymour\PHPWebApplicationFramework\crypt\KeypairedTrait;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoBoxPublicKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\ParentKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubtypeColumnTrait;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ConcreteSubtypeColumnInterface;

/**
 * defines a user-created group
 *
 * @author j
 *        
 */
class GroupData extends DataStructure implements ConcreteSubtypeColumnInterface, StaticTableNameInterface{

	use KeypairedTrait;
	use KeypairColumnsTrait;
	use NameColumnTrait;
	use ParentKeyColumnTrait;
	use StaticTableNameTrait;
	use SubtypeColumnTrait;
	
	public static function getDatabaseNameStatic():string{
		return "user_content";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		// group name
		$name = new NameDatum("name");
		// parent group
		$pk = new ForeignMetadataBundle("parent", $ds);
		$pk->setForeignDataStructureClass(GroupData::class);
		$pk->setNullable(true);
		$pk->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		$pk->constrain();
		// user who founded the group
		$founder = new UserMetadataBundle("founder", $ds);
		// group type
		$type = new StringEnumeratedDatum("subtype");
		$type->setNullable(true);
		
		// group public key
		$public = new SodiumCryptoBoxPublicKeyDatum("publicKey");
		$public->setUserWritableFlag(true);
		$public->setNullable(false);
		$public->setNeverLeaveServer(true);
		// role declarations
		$role_declarations = new KeyListDatum("role_declarations");
		$role_declarations->setConverseRelationshipKeyName("groupKey");
		$role_declarations->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_MANY);
		$role_declarations->volatilize();
		$role_declarations->setForeignDataStructureClass(RoleDeclaration::class);
		array_push($columns, $name, $pk, $founder, $type, $public, $role_declarations);
	}

	public function hasGroupType():bool{
		return $this->hasSubtype();
	}

	public function getGroupType():string{
		return $this->getSubtype();
	}

	public function setGroupType(string $value):string{
		return $this->setSubtype($value);
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array{
		$f = __METHOD__;
		$roles = parent::getUserRoles($mysqli, $user);
		$r2 = $user->getGroupRoles($mysqli, $this);
		if(!empty(array_intersect($r2, array_keys(mods()->getUserClasses())))){
			Debug::warning("{$f} getGroupRoles returned account types");
			return [
				USER_ROLE_ERROR => USER_ROLE_ERROR
			];
		}
		$roles = array_merge($roles, $r2);
		if($this->hasParentKey()){
			$parent = $this->acquireParentObject($mysqli);
			$r2 = $parent->getUserRoles($mysqli, $user);
			$roles = array_merge($roles, $r2);
		}
		return $roles;
	}

	public static function getPrettyClassName():string{
		return _("Group");
	}

	public static function getTableNameStatic(): string{
		return "groups";
	}

	public static function getDataType(): string{
		return DATATYPE_GROUP;
	}

	public static function getPrettyClassNames():string{
		return _("Groups");
	}

	public static function getPhylumName(): string{
		return "groups";
	}

	public function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		if(!$this->hasPrivateKey()){
			Debug::error("{$f} private key is undefined");
		}
		return parent::beforeInsertHook($mysqli);
	}

	protected function nullPrivateKeyHook(): int{
		$f = __METHOD__;
		Debug::error("{$f} private key is null");
		return FAILURE;
	}
}
