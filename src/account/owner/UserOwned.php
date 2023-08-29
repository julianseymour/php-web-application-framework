<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\owner;

use function JulianSeymour\PHPWebApplicationFramework\config;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\UserKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\account\UserMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\account\UserNameKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\account\shadow\ShadowUser;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use Exception;
use mysqli;

abstract class UserOwned extends DataStructure{

	use UserKeyColumnTrait;
	use UserNameKeyColumnTrait;

	public static function getDatabaseNameStatic():string{
		return "user_content";
	}
	
	public static function getPermissionStatic(string $name, $data){
		return new OwnerPermission($name);
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array{
		$roles = parent::getUserRoles($mysqli, $user);
		if ($this->isOwnedBy($user)) {
			$roles[USER_ROLE_OWNER] = USER_ROLE_OWNER;
		}
		if ($this->hasUserTemporaryRole()) {
			$temp = $this->getUserTemporaryRole();
			$roles[$temp] = $temp;
		}
		return $roles;
	}

	public function getUserTemporaryRole(){
		return $this->getColumnValue("userTemporaryRole");
	}

	public function setUserTemporaryRole($value){
		return $this->setColumnValue("userTemporaryRole", $value);
	}

	public function hasUserTemporaryRole():bool{
		return $this->hasColumnValue("userTemporaryRole");
	}

	public function isOwnedBy(UserData $user):bool{
		$f = __METHOD__;
		$print = false;
		if ($this->hasUserData()) {
			if ($print) {
				Debug::print("{$f} user data is defined");
			}
			$userkey = $this->getUserData()->getIdentifierValue();
		} elseif ($this->hasUserKey()) {
			if ($print) {
				Debug::print("{$f} user key is defined");
			}
			$userkey = $this->getUserKey();
		} else {
			if ($print) {
				Debug::print("{$f} neither user data or key are defined");
			}
			return false;
		}
		if ($print) {
			if ($userkey === $user->getIdentifierValue()) {
				Debug::print("{$f} yes, this object is owned by user with key \"{$userkey}\"");
			} else {
				Debug::print("{$f} no, this object is not owned by a user with key \"{$userkey}\"");
			}
		}
		return $userkey === $user->getIdentifierValue();
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$f = __METHOD__;
		try {
			$config = parent::getArrayMembershipConfiguration($config_id);
			switch ($config_id) {
				case CONST_DEFAULT:
				default:
					$config['userKey'] = $this->hasUserKey();
					break;
			}
			return $config;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPushNotificationWarranted():bool{
		return false;
	}

	public function getNormalizedEmailAddress():string{
		return $this->getUserData()->getNormalizedEmailAddress();
	}

	public function hasAdministrator():bool{
		return $this->getUserData()->hasAdministrator();
	}

	public function getCorrespondentKey():string{
		return $this->getUserData()->getCorrespondentKey();
	}

	public function hasCorrespondentObject():bool{
		return $this->getUserData()->hasCorrespondentObject();
	}

	public static function skipUserAcquisitionOnLoad():bool{
		return false;
	}

	public static function getUserAccountClassStatic(string $type):string{
		$f = __METHOD__;
		$print = false;
		if (! is_string($type)) {
			Debug::error("{$f} account type should be a string");
		}
		switch ($type) {
			case ACCOUNT_TYPE_ADMIN:
				Debug::print("{$f} admin it is");
				$class = config()->getAdministratorClass();
				break;
			case ACCOUNT_TYPE_USER:
				$class = config()->getNormalUserClass();
				break;
			case ACCOUNT_TYPE_GUEST:
				$class = config()->getGuestUserClass();
				break;
			case ACCOUNT_TYPE_SHADOW:
				$class = config()->getShadowUserClass();
				break;
			default:
				Debug::error("{$f} undefined account type \"{$type}\"");
				break;
		}
		if ($print) {
			Debug::print("{$f} returning user class \"{$class}\"");
		}
		return $class;
	}

	public function getLanguagePreference():string{
		return $this->getUserLanguagePreference();
	}

	public function getUserAccountTypeString():string{
		return UserData::getAccountTypeStringStatic($this->getUserAccountType());
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch ($column_name) {
			case "userDisplayName":
				return $this->hasUserDisplayName();
			case "userAccountTypeString":
				return $this->hasUserAccountType();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public function getVirtualColumnValue(string $column_name){
		switch ($column_name) {
			case "userAccountTypeString":
				return $this->getUserAccountTypeString();
			case "userName":
				return $this->getUserName();
			default:
				return parent::getVirtualColumnValue($column_name);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$bundle = new UserMetadataBundle("user", $ds);
		$bundle->setAutoloadFlag(true);
		$bundle->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		$bundle->constrain();
		// $enable = static::getIsEnabledDatum();
		static::pushTemporaryColumnsStatic($columns, $bundle);
	}

	public function getUserHardResetCount():int{
		if ($this->hasColumnValue("userHardResetCount")) {
			return $this->getColumnValue("userHardResetCount");
		}
		$user = $this->getUserData();
		return $this->setUserHardResetCount($user->getHardResetCount());
	}

	public function setUserHardResetCount(int $count):int{
		return $this->setColumnValue('userHardResetCount', $count);
	}

	public function getEmailAddress():string{
		return $this->getUserData()->getEmailAddress();
	}

	public static function getDefaultCollapseState():bool{
		return false;
	}

	public function getUserDataNumber():int{
		return $this->getUserData()->getSerialNumber();
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try {
			if (! $this->hasColumn("userAccountType") && ! $this->hasColumn("userHardResetCount")) {
				return parent::afterGenerateInitialValuesHook();
			} elseif ($this->hasUserData()) {
				$user = $this->getUserData();
				if ($this->hasColumn("userAccountType")) {
					$type = $user->getAccountType();
					$this->setUserAccountType($type);
				}
				if ($user->hasColumn("hardResetCount") && $this->hasColumn("userHardResetCount")) {
					$count = $user->getHardResetCount();
					$this->setUserHardResetCount($count);
				}
			} else {
				Debug::warning("{$f} user is undefined");
			}
			// Debug::print("{$f} returning normally");
			return parent::afterGenerateInitialValuesHook();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setUserLanguagePreference(string $lang):string{
		return $this->setColumnValue("userLanguagePreference", $lang);
	}

	public function getUserLanguagePreference():string{
		if ($this->hasColumnValue("userLanguagePreference")) {
			return $this->getColumnValue("userLanguagePreference");
		}
		$lang = $this->getUserData()->getLanguagePreference();
		if ($this->hasColumn("userLanguagePreference")) {
			return $this->setUserLanguagePreference($lang);
		}
		return $lang;
	}

	public function setUserName(string $value):string{
		$f = __METHOD__;
		$print = false; // $this instanceof LoginAttempt;
		if ($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		return $this->setColumnValue("userName", $value);
	}

	public function hasUserName():bool{
		return $this->hasColumnValue("userName");
	}

	public function getUserName():string{
		$f = __METHOD__;
		try {
			if ($this->hasColumn("userName") && $this->hasUserName()) {
				return $this->getColumnValue("userName");
			}
			if (! $this->hasUserData()) {
				$key = $this->getIdentifierValue();
				$class = $this->getClass();
				Debug::warning("{$f} user data is undefined for {$class} with key \"{$key}\"");
			}
			$user = $this->getUserData();
			if (! isset($user)) {
				Debug::error("{$f} user data returned null");
			}
			$name = $user->getName();
			// Debug::print("{$f} returning \"{$name}\"");
			return $this->hasColumn("userName") ? $this->setUserName($name) : $name;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasUserDisplayName():bool{
		if ($this->hasConcreteColumn("userDisplayName")) {
			return $this->getColumnValue("userDisplayName");
		} elseif ($this->hasUserData()) {
			$user = $this->getUserData();
			if ($user->hasColumn("displayName")) {
				return $user->hasDisplayName();
			}
		}
		return false;
	}

	public function getUserDisplayName():string{
		if ($this->hasConcreteColumn("userDisplayName")) {
			return $this->getConcreteColumn("userDisplayName");
		}
		return $this->getUserData()->getDisplayName();
	}

	public function hasUserNormalizedName():bool{
		return $this->hasColumnValue("userNormalizedName");
	}

	public function setUserNormalizedName(string $name):string{
		return $this->setColumnValue("userNormalizedName", $name);
	}

	public function getUserNormalizedName():string{
		if ($this->hasColumnValue("userNormalizedName")) {
			return $this->getColumnValue("userNormalizedName");
		}
		$user = $this->getUserData();
		return $this->setUserNormalizedName($user->getNormalizedName());
	}
}
