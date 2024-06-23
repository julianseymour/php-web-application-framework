<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\cidr_match;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\OwnerPermission;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\IntegerEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyTrait;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\SecurityNotificationData;
use JulianSeymour\PHPWebApplicationFramework\security\StoredIpAddress;
use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttemptClassResolver;
use Exception;
use mysqli;

class ListedIpAddress extends StoredIpAddress implements EmailNoteworthyInterface{

	use EmailNoteworthyTrait;

	public static function getPermissionStatic($name, $listed_ip){
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_INSERT:
				return new Permission($name, function (PlayableUser $user, ListedIpAddress $object, ...$params){
					if($object->hasList() && ! $object->isOwnedBy($user)){
						return FAILURE;
					}
					return SUCCESS;
				});
			case DIRECTIVE_PREINSERT_FOREIGN:
				return SUCCESS;
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_DELETE:
				return new OwnerPermission($name);
			default:
				Debug::error("{$f} invalid permission name \"{$name}\"");
		}
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		switch($config_id){
			case 'cache':
				return [
					'cidr' => true,
					'list' => true
				];
			case CONST_DEFAULT:
			default:
				$config = parent::getArrayMembershipConfiguration($config_id);
				$config['insertIpAddress'] = true;
				return $config;
		}
	}

	public function getVirtualColumnValue(string $column_name){
		switch($column_name){
			case "cidr":
				return $this->getCidrNotation();
			default:
				return parent::getVirtualColumnValue($column_name);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch($column_name){
			case "cidr":
				return $this->hasIpAddress() && $this->hasMask();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public static function getEmailNotificationClass():?string{
		return ErrorMessage::unimplemented(__METHOD__);
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		if(!$this->hasLastAttemptObject()){
			$timestamp = time();
		}else{
			$attempt = $this->getLastAttemptObject();
			$timestamp = $attempt->generateInsertTimestamp();
		}
		$this->setLastAttemptTimestamp($timestamp);
		return parent::afterGenerateInitialValuesHook();
	}

	public function getUpdateNotificationRecipient():UserData{
		return $this->getUserData();
	}

	public static function getNotificationClass(): string{
		return SecurityNotificationData::class;
	}

	public function hasLastAttemptObject(){
		return $this->hasForeignDataStructure("lastAttemptKey");
	}

	public function getLastAttemptObject(){
		return $this->getForeignDataStructure("lastAttemptKey");
	}

	public static function getTableNameStatic(): string{
		return "listed_ip_addresses";
	}
	
	public function cidrMatchYourself(?string $match_ip = null): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($match_ip === null){
				if($print){
					Debug::print("{$f} IP address not provided, assuming you want to match for the current IP address");
				}
				$match_ip = $_SERVER['REMOTE_ADDR'];
			}elseif($print){
				Debug::print("{$f} matching for IP address {$match_ip}");
			}
			if($this->getObjectStatus() === STATUS_DELETED){
				if($print){
					Debug::print("{$f} this IP address is about to be deleted");
				}
				return RESULT_CIDR_UNMATCHED;
			}
			$range = $this->getCidrNotation();
			if(! cidr_match($match_ip, $range)){
				if($print){
					Debug::print("{$f} no match");
				}
				return RESULT_CIDR_UNMATCHED;
			}elseif($print){
				Debug::print("{$f} CIDR match for IP range \"{$range}\"");
			}
			$list = $this->getList();
			switch($list){
				case POLICY_ALLOW:
					if($print){
						Debug::print("{$f} this IP address is whitelisted");
					}
					return SUCCESS;
				case POLICY_BLOCK:
					Debug::warning("{$f} this IP address is blacklisted");
					return ERROR_IP_ADDRESS_BLOCKED_BY_USER;
				case POLICY_DEFAULT:
					if($print){
						Debug::print("{$f} default list policy");
					}
					$tco = $this->getUserData();
					$policy = $tco->getFilterPolicy();
					if($policy === POLICY_BLOCK){
						Debug::warning("{$f} log it here and block them");
						return ERROR_IP_ADDRESS_NOT_AUTHORIZED;
					}elseif($print){
						Debug::print("{$f} user does not have whitelist mode enabled");
					}
					return RESULT_CIDR_UNMATCHED;
				default:
					Debug::error("{$f} illegal list policy \"{$list}\"");
					return FAILURE;
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function preventSelfLockout(): int{
		$f = __METHOD__;
		try{
			$print = false;
			$list = $this->getList();
			if($print){
				Debug::print("{$f} value is \"{$list}\" something other than whitelist");
			}
			$user = $this->getUserData();
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if($mysqli == null){
				return $this->setObjectStatus(static::debugErrorStatic($f, ERROR_MYSQL_CONNECT));
			}
			$status = $user->filterIpAddress($mysqli, $_SERVER['REMOTE_ADDR']);
			switch($status){
				case FAILURE:
				case ERROR_IP_ADDRESS_BLOCKED_BY_USER:
					Debug::warning("{$f} validateIpAddress failed");
					// $this->setList($old_list);
					return $this->setObjectStatus(ERROR_FILTER_LOCKED_OUT);
				case SUCCESS:
					Debug::print("{$f} IP validated successfully");
					return SUCCESS;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} filterIpAddress returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getCompositeUniqueColumnNames(): ?array{
		return [
			[
				"ipAddress",
				"mask",
				"userKey"
			]
		];
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::declareColumns($columns, $ds);
			$mask = new CidrMaskDatum("mask", 8);
			$list = new StringEnumeratedDatum("list", 8);
			$map = [
				POLICY_NONE,
				POLICY_ALLOW,
				POLICY_BLOCK
			];
			$list->setValidEnumerationMap($map);
			$note = new TextDatum("note"); // XXX encrypt this
			$note->setNl2brFlag(true);
			$note->setNullable(true);
			$note->setDefaultValue(null);
			$note->setUserWritableFlag(true);
			$note->setHumanReadableName(_("Note"));
			$attempted = new BooleanDatum("wasAccessAttempted");
			$attempted->setDefaultValue(false);
			$attempt_key = new ForeignMetadataBundle("lastAttempt", $ds);
			$attempt_key->setForeignDataStructureClassResolver(AccessAttemptClassResolver::class);
			$attempt_key->setNullable(true);
			$attempt_key->setDefaultValue(null);
			$attempt_key->constrain();
			$attempt_key->setOnUpdate(REFERENCE_OPTION_CASCADE);
			$attempt_key->setOnDelete(REFERENCE_OPTION_SET_NULL);
			$attempt_key->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);

			$attempt_result = new IntegerEnumeratedDatum("lastAttemptResult", 16);
			$attempt_result->setNullable(true);
			$attempt_result->setDefaultValue(null);
			$attempt_result->setAlwaysValidFlag(true);
			$attempt_ts = new TimestampDatum("lastAttemptTimestamp");
			$attempt_ts->setNullable(true);
			$attempt_ts->setDefaultValue(null);
			$attempt_success = new BooleanDatum("lastAttemptSuccessful");
			$attempt_success->setNullable(true);
			$attempt_success->setDefaultValue(null);
			$cidr = new VirtualDatum("cidr");
			array_push(
				$columns, 
				$mask, $list, $note, $attempted, $attempt_key, $attempt_result, $attempt_ts, $attempt_success, $cidr
			);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"disableNotifications",
			"dismissNotification"
		]);
	}

	public function isPushNotificationWarranted():bool{
		return true;
	}

	public function setLastAttemptTimestamp(int $value):int{
		return $this->setColumnValue("lastAttemptTimestamp", $value);
	}

	public function setLastAttemptResult(int $value):int{
		return $this->setColumnValue("lastAttemptResult", $value);
	}

	public function setLastAttemptKey(string $value):string{
		return $this->setColumnValue("lastAttemptKey", $value);
	}

	public function setAccessAttempted(bool $value):bool{
		return $this->setColumnValue("wasAccessAttempted", $value);
	}

	public function setLastAttemptDataType(string $value):string{
		return $this->setColumnValue("lastAttemptDataType", $value);
	}

	public function getLastAttemptTimestamp():int{
		return $this->getColumnValue("lastAttemptTimestamp");
	}

	public function getLastAttemptResult():int{
		return $this->getColumnValue("lastAttemptResult");
	}

	public function getLastAttemptKey():string{
		return $this->getColumnValue("lastAttemptKey");
	}

	public function getAccessAttempted():bool{
		return $this->getColumnValue("wasAccessAttempted");
	}

	public function getLastAttemptDataType():string{
		return $this->getColumnValue("lastAttemptDataType");
	}

	public function getList():string{
		return $this->getColumnValue("list");
	}

	public function setList(string $list):string{
		return $this->setColumnValue("list", $list);
	}

	public function getName():string{
		return $this->getCidrNotation();
	}

	public function getLastAttemptSubtype():string{
		return $this->getColumnValue("lastAttemptSubtype");
	}

	public function hasList():bool{
		return $this->hasColumnValue("list") && $this->getColumnValue("list") !== POLICY_DEFAULT;
	}

	public function hasLastAttemptSubtype():bool{
		return $this->hasColumnValue("lastAttemptSubtype");
	}

	public function setLastAttemptSubtype(string $value):string{
		return $this->setColumnValue("lastAttemptSubtype", $value);
	}

	/**
	 *
	 * @param AccessAttempt $attempt
	 * @return AccessAttempt
	 */
	public function setLastAttemptObject($attempt){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} setting last attempt object");
		}
		$this->setLastAttemptDataType($attempt->getDataType());
		$this->setLastAttemptSubtype($attempt->getSubtype());
		if(!$attempt->hasIdentifierValue()){
			$ip_address = $this;
			$index = sha1(random_bytes(32));
			$closure = function ($event, $target) use ($ip_address, $attempt, $index){
				$key = $event->getProperty('uniqueKey');
				$ip_address->setLastAttemptKey($key);
				$attempt->removeEventListener(EVENT_AFTER_GENERATE_KEY, $index);
			};
			$attempt->addEventListener(EVENT_AFTER_GENERATE_KEY, $closure, $index);
		}
		return $this->setForeignDataStructure("lastAttemptKey", $attempt);
	}

	public function getCidrNotation(){
		$f = __METHOD__;
		try{
			$ip = $this->getIpAddress();
			if(preg_match(REGEX_IPv4_ADDRESS, $ip)){
				$mask = $this->getMask();
				if(!is_int($mask) || $mask < 0 || $mask > 32){
					Debug::error("{$f} illegal mask \"{$mask}\"");
				}
			}elseif(preg_match(REGEX_IPv6_ADDRESS, $ip)){
				Debug::error("{$f} IPV6 is unsupported");
			}else{
				Debug::warning("{$f} illegal IP address \"{$ip}\"");
				return null;
			}
			return "{$ip}/{$mask}";
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setMask(int $mask): int{
		$f = __METHOD__;
		try{
			if(!is_int($mask)){
				Debug::error("{$f} mask \"{$mask}\" is not an integer");
			}elseif($mask > 128){
				Debug::error("{$f} illegal mask \"{$mask}\"");
			}
			return $this->setColumnValue("mask", $mask);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getMask(): int{
		return $this->getColumnValue('mask');
	}

	public function hasMask():bool{
		return $this->hasColumnValue("mask");
	}

	public function getNote():string{
		return $this->getColumnValue('note');
	}

	public function setNote(string $note):string{
		return $this->setColumnValue("note", $note);
	}

	public static function getPhylumName(): string{
		return "listedIpAddresses";
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_PSEUDOKEY;
	}

	public function getNotificationPreview(){
		$reason = $this->getReasonLoggedString();
		$ip = $this->getIpAddress();
		return substitute(_("%1% from %2%"), $reason, $ip);
	}

	public function beforeDeleteHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$backup = $this->getObjectStatus();
		$this->setObjectStatus(STATUS_SKIP_ME);
		$user = $this->getUserData();
		$status = $user->filterIpAddress($mysqli, $_SERVER['REMOTE_ADDR'], true);
		$this->setObjectStatus($backup);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} filterIpAddress returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return parent::beforeDeleteHook($mysqli);
	}

	public function hasLastAttemptKey():bool{
		return $this->hasColumnValue("lastAttemptKey");
	}

	public function getCounterpartObject(){
		return $this;
	}

	public function beforeInsertHook(mysqli $mysqli):int{
		$f = __METHOD__;
		if($this->getList() === POLICY_BLOCK){
			$user = user();
			$phylum = ListedIpAddress::getPhylumName();
			$children = user()->hasForeignDataStructureList($phylum) ? user()->getForeignDataStructureList($phylum) : [];
			$children[$this->getIdentifierValue()] = $this;
			$children = uasort($children, function ($a, $b){
				if($a->getMask() > $b->getMask()){
					return 1;
				}
				return -1;
			});
			$user->setChildren($phylum, $children);
			$status = $user->filterIpAddress($mysqli, $_SERVER['REMOTE_ADDR'], true);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} filterIpAddress returned error message \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		return parent::beforeInsertHook($mysqli);
	}

	public function isNotificationDataWarranted(PlayableUser $user): bool{
		return true;
	}

	public function getConfirmationUri(){
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function isEmailNotificationWarranted(UserData $recipient): bool{
		return false;
	}

	public static function getSubtypeStatic(): string{
		return IP_ADDRESS_TYPE_LISTED;
	}
	
	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null):void{
		parent::reconfigureColumns($columns, $ds);
		$columns['userKey']->setConverseRelationshipKeyName(static::getPhylumName());
	}
}
