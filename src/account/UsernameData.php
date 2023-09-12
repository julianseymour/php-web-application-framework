<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\owner\OwnerPermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NormalizedNameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class UsernameData extends DataStructure implements StaticTableNameInterface{

	use NormalizedNameColumnTrait;
	use StaticTableNameTrait;
	use UserKeyColumnTrait;

	public static function getDatabaseNameStatic():string{
		return "usernames";
	}
	
	public function __construct(?int $mode = ALLOCATION_MODE_EAGER){
		$f = __METHOD__;
		parent::__construct($mode);
		if($mode === ALLOCATION_MODE_EAGER) {
			if(!$this->getColumn("userAccountType")->getRetainOriginalValueFlag()) {
				Debug::error("{$f} user account type does not retain original value");
			}
		}
	}

	public static function getCompositeUniqueColumnNames(): ?array{
		return [
			[
				"normalizedName"
			]
		];
	}

	public static function getDataType(): string{
		return DATATYPE_USERNAME;
	}

	public static function getPhylumName(): string{
		return "usernames";
	}

	public static function getPrettyClassName():string{
		return _("Username");
	}

	public static function getTableNameStatic(): string{
		return "usernames";
	}

	public static function getPrettyClassNames():string{
		return _("Usernames");
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);

		$userKey = new ForeignKeyDatum("userKey");
		$userKey->setForeignDataStructureClassResolver(UserClassResolver::class);
		$userKey->setForeignDataType(DATATYPE_USER);
		$userKey->setForeignDataSubtypeName("userAccountType");
		$userKey->setIndexFlag(true);
		$userKey->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$userKey->setAutoloadFlag(true);
		$userKey->constrain()->retainOriginalValue();
		$userKey->setConverseRelationshipKeyName("userNameKey");

		$type = new StringEnumeratedDatum("userAccountType");
		$type->setValidEnumerationMap([
			ACCOUNT_TYPE_USER,
			ACCOUNT_TYPE_ADMIN,
			ACCOUNT_TYPE_DEVELOPER,
			ACCOUNT_TYPE_GROUP,
			ACCOUNT_TYPE_GUEST,
			ACCOUNT_TYPE_HELPDESK,
			ACCOUNT_TYPE_SHADOW,
			ACCOUNT_TYPE_TRANSLATOR
		]);
		$type->retainOriginalValue();

		$name = new NameDatum('name');
		$name->setSearchable(true);
		$normalized = new TextDatum("normalizedName");
		$normalized->setCase(CASE_LOWER);
		$normalized->setSortable(true);
		$normalized->setSearchable(true);
		$closure = function ($event, $target) use ($normalized) {
			$value = $event->getProperty('value');
			if(!empty($value)) {
				$normalized->setValue(NameDatum::normalize($value));
			}
		};
		$name->addEventListener(EVENT_AFTER_SET_VALUE, $closure);
		$display_name = new NameDatum("displayName");
		$display_name->setNullable(true);
		$display_name->setDefaultValue(null);
		$display_name->setSearchable(true);
		$closure = function ($event, $target) use ($ds) {
			$columnName = $event->getProperty("columnName");
			if($columnName !== "userKey") {
				return;
			}
			$struct = $event->getProperty("data");
			if($struct->hasAccountType()) {
				$ds->setColumnValue("userAccountType", $struct->getAccountType());
			}
		};
		$ds->addEventListener(EVENT_AFTER_SET_FOREIGN, $closure);
		array_push($columns, $userKey, $type, $name, $normalized, $display_name);
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_HASH;
	}

	public static function getPermissionStatic($name, $object){
		switch ($name) {
			case DIRECTIVE_INSERT:
				return new AnonymousAccountTypePermission($name);
			case DIRECTIVE_UPDATE:
				return new OwnerPermission($name);
			default:
				return parent::getPermissionStatic($name, $object);
		}
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array{
		$roles = parent::getUserRoles($mysqli, $user);
		if($this->isOwnedBy($user)) {
			$roles[USER_ROLE_OWNER] = USER_ROLE_OWNER;
		}
		return $roles;
	}

	public function isOwnedBy(UserData $user):bool{
		$f = __METHOD__;
		$print = false;
		if($this->hasUserData()) {
			if($print) {
				Debug::print("{$f} user data is defined");
			}
			$userkey = $this->getUserData()->getIdentifierValue();
		}elseif($this->hasUserKey()) {
			if($print) {
				Debug::print("{$f} user key is defined");
			}
			$userkey = $this->getUserKey();
		}else{
			if($print) {
				Debug::print("{$f} neither user data or key are defined");
			}
			return false;
		}
		if($print) {
			if($userkey === $user->getIdentifierValue()) {
				Debug::print("{$f} yes, this object is owned by user with key \"{$userkey}\"");
			}else{
				Debug::print("{$f} no, this object is not owned by a user with key \"{$userkey}\"");
			}
		}
		return $userkey === $user->getIdentifierValue();
	}
}
