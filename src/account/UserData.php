<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\default_lang_region;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\timezone_offset;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\correspondent\CorrespondentKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\account\group\GroupData;
use JulianSeymour\PHPWebApplicationFramework\account\group\GroupInvitation;
use JulianSeymour\PHPWebApplicationFramework\account\role\RoleDeclaration;
use JulianSeymour\PHPWebApplicationFramework\account\role\UserRoleData;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ConcreteSubtypeColumnInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoBoxPublicKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NormalizedNameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubtypeColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\input\choice\SelectInput;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use DateTimeZone;
use Exception;
use mysqli;

abstract class UserData extends DataStructure implements ConcreteSubtypeColumnInterface, StaticSubtypeInterface, StaticTableNameInterface{

	use CorrespondentKeyColumnTrait;
	use EmailAddressColumnTrait;
	use NormalizedNameColumnTrait;
	use StaticTableNameTrait;
	use SubtypeColumnTrait;
	
	public static function getDatabaseNameStatic():string{
		return "accounts";
	}
	
	public static function getSubtypeStatic():string{
		return ACCOUNT_TYPE_UNDEFINED;
	}
	
	public function loadFailureHook():int{
		$this->setAccountType(CONST_ERROR);
		return parent::loadFailureHook();
	}

	public function getUserRoles(mysqli $mysqli, UserData $user):?array{
		$roles = parent::getUserRoles($mysqli, $user);
		if($this->hasIdentifierValue() && $this->getIdentifierValue() === $user->getIdentifierValue()){ //$this->equals($this, $user)){
			$roles['self'] = 'self';
		}
		return $roles;
	}

	public function getSubtype():string{
		if($this->hasColumnValue('subtype')){
			return $this->getColumnValue('subtype');
		}
		return $this->setSubtype(static::getSubtypeStatic());
	}

	public function getStaticRoles():?array{
		return [
			$this->getSubtype() => $this->getSubtype()
		];
	}

	public function getAccountType():string{
		return $this->getSubtype();
	}

	public function getPreferredLanguageName():string{
		return default_lang_region($this->getLanguagePreference());
	}

	public static function getAccountTypeStringStatic(string $account_type):?string{
		$f = __METHOD__;
		try{
			switch($account_type){
				case ACCOUNT_TYPE_ERROR:
					return _("Error");
				case ACCOUNT_TYPE_ADMIN:
					return _("Administrator");
				case ACCOUNT_TYPE_USER:
					return _("Registered");
				case ACCOUNT_TYPE_GUEST:
					return _("Guest");
				case ACCOUNT_TYPE_DEVELOPER:
					return _("Developer");
				case ACCOUNT_TYPE_TRANSLATOR:
					return _("Translator");
				case ACCOUNT_TYPE_HELPDESK:
					return _("Help desk");
				case ACCOUNT_TYPE_SHADOW:
					return _("Shadow");
				default:
					Debug::error("{$f} invalid account type \"{$account_type}\"");
					return null;
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getAccountTypeString(){
		return static::getAccountTypeStringStatic($this->getSubtype());
	}

	public function hasUserData():bool{
		return true;
	}

	public function getParentDataType(){
		return DATATYPE_USER;
	}

	public function getUserKey():string{
		return $this->getIdentifierValue();
	}

	public function getEmailAddress():string{
		return $this->getColumnValue('emailAddress');
	}

	public static final function getDataType(): string{
		return DATATYPE_USER;
	}

	public function setAccountType(string $value):string{
		return $this->setSubtype($value);
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$f = __METHOD__;
		try{
			$config = parent::getArrayMembershipConfiguration($config_id);
			switch($config_id){
				case CONST_DEFAULT:
					$config['subtype'] = true;
					$config['accountTypeString'] = true;
				default:
					return $config;
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasAccountType():bool{
		return $this->hasSubtype();
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::declareColumns($columns, $ds);
			$account_type = new StringEnumeratedDatum("subtype");
			$account_type->setHumanReadableName(_("Account type"));
			$account_type->setAdminInterfaceFlag(true);
			$account_type->setElementClass(SelectInput::class);
			$account_type->setValidEnumerationMap(array_keys(mods()->getUserClasses()));
			$account_type->setDefaultValue($ds->getSubtypeStatic());
			$language = new StringEnumeratedDatum("languagePreference");
			$language->setValidEnumerationMap(config()->getSupportedLanguages());
			$language->setUserWritableFlag(true);
			$language->setHumanReadableName(_("Language preference"));
			$language->setValue(LANGUAGE_DEFAULT);
			$account_str = new VirtualDatum("accountTypeString");
			$timezone = new TextDatum("timezone");
			$timezone->setNullable(true);
			$correspondentKey = new UserMetadataBundle("correspondent", $ds);
			$correspondentKey->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
			$correspondentKey->volatilize();
			$correspondentPublicKey = new SodiumCryptoBoxPublicKeyDatum("correspondentPublicKey");
			$correspondentPublicKey->volatilize();
			$temporaryRole = new StringEnumeratedDatum("temporaryRole");
			$temporaryRole->volatilize();
			$country_code = new StringEnumeratedDatum("regionCode");
			$country_code->setNullable(false);
			array_push($columns, $account_type, $language, $account_str, $timezone, $correspondentKey, $correspondentPublicKey, $temporaryRole, $country_code);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setRegionCode(string $code):string{
		return $this->setColumnValue("regionCode", $code);
	}
	
	public function getRegionCode():string{
		return $this->getColumnValue("regionCode");
	}
	
	public function hasRegionCode():bool{
		return $this->hasColumnValue("regionCode");
	}

	public function getLocaleString():string{
		return $this->getLanguagePreference()."_".$this->getRegionCode();
	}
	
	public function timezone_offset(){
		if(!$this->hasTimezone()){
			return 0;
		}
		$server_timezone = new DateTimeZone(date_default_timezone_get());
		$user_timezone = new DateTimeZone($this->getTimezone());
		return timezone_offset($server_timezone, $user_timezone);
	}

	public function hasTimezone():bool{
		return $this->hasColumnValue("timezone");
	}

	public function getTimezone(){
		return $this->hasTimezone() ? $this->getColumnValue("timezone") : date_default_timezone_get();
	}

	public function setTimezone($timezone){
		return $this->setColumnValue("timezone", $timezone);
	}

	public function hasLanguagePreference():bool{
		return $this->hasColumnValue("languagePreference");
	}

	public function getLanguagePreference():string{
		return $this->getColumnValue("languagePreference");
	}

	public function setLanguagePreference(string $language):string{
		return $this->setColumnValue("languagePreference", $language);
	}

	public static final function getPhylumName(): string{
		return "users";
	}

	public static function isParentRequiredStatic($that = null):bool{
		return false;
	}

	public final function getUserData(): UserData{
		return $this;
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		try{
			switch($column_name){
				case "accountTypeString":
					return $this->getAccountTypeString();
				case 'subtype':
					return $this->getSubtypeStatic();
				default:
					return parent::getVirtualColumnValue($column_name);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch($column_name){
			case "accountTypeString":
				return $this->hasAccountType();
			case 'subtype':
				return true;
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public function setTemporaryRole($role){
		$f = __METHOD__;
		try{
			if(empty($role)){
				Debug::error("{$f} received null parameter");
			}
			// Debug::print("{$f} returning \"{$role}\"");
			return $this->setColumnValue("temporaryRole", $role);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasTemporaryRole():bool{
		return $this->hasColumnValue("temporaryRole");
	}

	public function getTemporaryRole(){
		$f = __METHOD__;
		if(!$this->hasTemporaryRole()){
			Debug::error("{$f} role is undefined");
		}
		return $this->getColumnValue("temporaryRole");
	}

	/**
	 * returns a list of roles that this user has within the provided group
	 *
	 * @param mysqli $mysqli
	 * @param GroupData $group
	 * @return array|NULL
	 */
	public function getGroupRoles(mysqli $mysqli, GroupData $group): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasGroupRoles($group)){
				return $this->groupRoles[$group->getIdentifierValue()];
			}
			$roles = [];
			if($this->getIdentifierValue() === $this->getFounderKey()){
				$roles[USER_ROLE_FOUNDER] = USER_ROLE_FOUNDER;
			}
			// if the user has an invitation, they're a member. Otherwise they're a stranger
			$invitation = GroupInvitation::selectStatic(null, "groupKey", "userKey", "revoked")->where(new AndCommand(new WhereCondition("groupKey", OPERATOR_EQUALS), new WhereCondition("userKey", OPERATOR_EQUALS)))
				->orderBy(new OrderByClause("insertTimestamp", DIRECTION_DESCENDING))
				->limit(1)
				->prepareBindExecuteGetResult($mysqli, 'ss', [
				$group->getIdentifierValue(),
				$this->getIdentifierValue()
			]);
			if(is_array($invitation)){
				if($print){
					Debug::print("{$f} query results have been cached");
				}
				$count = count($invitation);
			}else{
				$count = $invitation->num_rows;
				$invitation = $invitation->fetch_all(MYSQLI_ASSOC);
			}
			if($count === 0){
				if($print){
					Debug::print("{$f} invitation does not exist");
				}
				$roles['stranger'] = 'stranger';
				return $this->setGroupRoles($group, $roles);
			}elseif($print){
				Debug::print("{$f} this user is a member of the group");
			}
			$roles['member'] = 'member';
			// get the names of all this user's roles in the group that have not expired
			global $__START;
			$names = UserRoleData::selectStatic(null, "name")->where(new AndCommand(
				// refers to this user
				new WhereCondition("userKey", OPERATOR_EQUALS), 
				// role has not expired
				new OrCommand(new WhereCondition("expirationTimestamp", OPERATOR_IS_NULL), new WhereCondition("expirationTimestamp", OPERATOR_GREATERTHAN)), 
				// groupKey = this group's key
				new WhereCondition("roleKey", OPERATOR_IN, 
					// 's',
					RoleDeclaration::selectStatic(null, RoleDeclaration::getIdentifierNameStatic())->where(new WhereCondition("groupKey", OPERATOR_EQUALS)))))
				->prepareBindExecuteGetResult($mysqli, 'sis', [
				$this->getIdentifierValue(),
				$__START,
				$group->getIdentifierValue()
			]);
			if(!is_array($names)){
				$names = $names->fetch_all(MYSQLI_ASSOC);
			}elseif($print){
				Debug::print("{$f} query results have been cached");
			}
			$temp = [];
			foreach($names as $r){
				$name = RoleDeclaration::escapeCustomRoleName($r['name']);
				$temp[$name] = $name;
			}
			unset($names);
			// verify none of them encroach on built-in roles or account types
			if(!empty(array_intersect($temp, array_merge(app()->getReservedRoles(), array_keys(mods()->getUserClasses()))))){
				Debug::warning("{$f} list of role names includes reserved roles and/or account types");
				return [
					USER_ROLE_ERROR => USER_ROLE_ERROR
				];
			}elseif($print){
				Debug::print("{$f} none of the custom roles conflict with reserved roles or account types");
			}
			$roles = array_merge($roles, $temp);
			if($print){
				Debug::print("{$f} returning the following roles:");
				Debug::printArray($roles);
			}
			return $this->setGroupRoles($group, $roles);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasGroupRoles($group): bool{
		if($group instanceof GroupData){
			$group = $group->getIdentifierValue();
		}
		return isset($this->groupRoles) && is_array($this->groupRoles) && array_key_exists($group, $this->groupRoles);
	}
}
