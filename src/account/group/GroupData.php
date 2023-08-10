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
use mysqli;

/**
 * defines a user-created group
 *
 * @author j
 *        
 */
class GroupData extends DataStructure
{

	use KeypairedTrait;
	use KeypairColumnsTrait;
	use NameColumnTrait;
	use ParentKeyColumnTrait;

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
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
		$type = new StringEnumeratedDatum("groupType");
		$type->setNullable(true); // DefaultValue(CONST_NONE);
		                          // group private key
		/*
		 * $privateKey = new BlobDatum("privateKey");
		 * $privateKey->setNullable(false);
		 * $privateKey->setNeverLeaveServer(true);
		 * $privateKey->setEncryptionScheme(AsymmetricEncryptionScheme::class);
		 * $privateKey->volatilize();
		 */
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
		static::pushTemporaryColumnsStatic($columns, $name, $pk, $founder, $type, $public, $role_declarations);
	}

	public function hasSubtypeValue(): bool
	{
		return $this->hasGroupType();
	}

	public function getSubtypeValue()
	{
		return $this->getGroupType();
	}

	public function hasGroupType()
	{
		return $this->hasColumnValue("groupType");
	}

	public function getGroupType()
	{
		return $this->getColumnValue("groupType");
	}

	public function setGroupType($value)
	{
		return $this->setColumnValue("groupType", $value);
	}

	public function setSubtype($value)
	{
		return $this->setGroupType($value);
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array
	{
		$f = __METHOD__; //GroupData::getShortClass()."(".static::getShortClass().")->getUserRoles()";
		$roles = parent::getUserRoles($mysqli, $user);
		$r2 = $user->getGroupRoles($mysqli, $this);
		if (! empty(array_intersect($r2, array_keys(mods()->getUserClasses())))) {
			Debug::warning("{$f} getGroupRoles returned account types");
			return [
				USER_ROLE_ERROR => USER_ROLE_ERROR
			];
		}
		$roles = array_merge($roles, $r2);
		if ($this->hasParentKey()) {
			$parent = $this->acquireParentObject($mysqli);
			$r2 = $parent->getUserRoles($mysqli, $user);
			$roles = array_merge($roles, $r2);
		}
		return $roles;
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Group");
	}

	public static function getTableNameStatic(): string
	{
		return "groups";
	}

	public static function getDataType(): string
	{
		return DATATYPE_GROUP;
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Groups");
	}

	public static function getPhylumName(): string
	{
		return "groups";
	}

	public function beforeInsertHook(mysqli $mysqli): int
	{
		$f = __METHOD__; //GroupData::getShortClass()."(".static::getShortClass().")->beforeInsertHook()";
		if (! $this->hasPrivateKey()) {
			Debug::error("{$f} private key is undefined");
		}
		return parent::beforeInsertHook($mysqli);
	}

	protected function nullPrivateKeyHook(): int
	{
		$f = __METHOD__; //GroupData::getShortClass()."(".static::getShortClass().")->nullPrivateKeyHook()";
		Debug::error("{$f} private key is null");
		return FAILURE;
	}
}
