<?php

namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\language\settings\DetectLocaleUseCase;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;

class SessionRecoveryCookie extends DataStructure{

	public static function getDatabaseNameStatic():string{
		return "user_content";
	}
	
	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public function setCookieSecret($secret){
		return $this->setColumnValue(static::getCookieSecretColumnName(), $secret);
	}

	public function hasCookieSecret(){
		return $this->hasColumnValue(static::getCookieSecretColumnName());
	}

	public function getCookieSecret(){
		return $this->getColumnValue(static::getCookieSecretColumnName());
	}

	public function hasRecoveryKey(){
		return $this->hasColumnValue(static::getRecoveryKeyColumnName());
	}

	public function getRecoveryKey(){
		$f = __METHOD__;
		if (! $this->hasRecoveryKey()) {
			Debug::error("{$f} recovery key is undefined");
		}
		return $this->getColumnValue(static::getRecoveryKeyColumnName());
	}

	public function setRecoveryKey($key){
		return $this->setColumnValue(static::getRecoveryKeyColumnName(), $key);
	}

	public function hasSessionRecoveryData(){
		return $this->hasForeignDataStructure(static::getRecoveryKeyColumnName());
	}

	public function getSessionRecoveryData(){
		return $this->getForeignDataStructure(static::getRecoveryKeyColumnName());
	}

	public function setSessionRecoveryData($srd){
		return $this->setForeignDataStructure(static::getRecoveryKeyColumnName(), $srd);
	}

	public function hasValidRecoveryData(){
		$f = __METHOD__;
		try {
			if (! $this->hasRecoveryKey()) {
				return false;
			} elseif ($this->hasSessionRecoveryData()) {
				$srd = $this->getSessionRecoveryData();
			} else {
				$recovery_key = $this->getRecoveryKey();
				$mysqli = db()->getConnection(PublicReadCredentials::class);
				$srd = new SessionRecoveryData();
			}
			$kit = $srd->unpack($mysqli, $recovery_key);
			if (is_array($kit)) {
				if (! $this->hasSessionRecoveryData()) {
					$this->setSessionRecoveryData($srd);
				}
				return true;
			}
			return false;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected static function getCookieSecretColumnName():string{
		return "cookieSecret";
	}

	protected static function getRecoveryKeyColumnName():string{
		return "recoveryKey";
	}

	public function recoverSession():?PlayableUser{
		$f = __METHOD__;
		try {
			$print = false;
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if (! isset($mysqli)) {
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::warning("{$f} {$err}");
				return null;
			}
			// load session recovery data from database
			$recovery_key = $this->getRecoveryKey();
			if ($print) {
				Debug::print("{$f} session recovery cookie has its recovery key set to \"{$recovery_key}\"");
			}
			$recovery_data = new SessionRecoveryData();
			$recovery_data->setSessionRecoveryCookie($this);
			$kit = $recovery_data->unpack($mysqli, $recovery_key);
			if (! is_array($kit)) {
				$status = $recovery_data->getObjectStatus();
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} unpacking session recovery data with key \"{$recovery_key}\" returned error status \"{$err}\"");
				return null;
			}
			$user_key = $kit["userKey"];
			$user_type = $kit['userAccountType'];
			$deterministicSecretKey = base64_decode($kit['deterministicSecretKey_64']);
			// load user data
			$user_class = mods()->getUserClass($user_type);
			DetectLocaleUseCase::detectLocaleStatic();
			$user = new $user_class(ALLOCATION_MODE_SUBJECTIVE);
			$idn = $user->getIdentifierName();
			$result = $user->select()->where(new WhereCondition($idn, OPERATOR_EQUALS))->withTypeSpecifier($user->getColumn($idn)->getTypeSpecifier())->withParameters($user_key)->executeGetResult($mysqli);
			$count = $result->num_rows;
			if ($count !== 1) {
				$result->free_result();
				Debug::warning("{$f} {$user_class} with key \"{$user_key}\" count is {$count}");
				return null;
			}
			$results = $result->fetch_all(MYSQLI_ASSOC)[0];
			$result->free_result();
			$status = $user->processQueryResultArray($mysqli, $results);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
				$user->setObjectStatus($status);
				return null;
			}
			$status = $user->loadIntersectionTableKeys($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loadIntersectionTableKeys returned error status \"{$err}\"");
				$user->setObjectStatus($status);
				return null;
			} elseif (cache()->enabled() && USER_CACHE_ENABLED) {
				$columns = $user->getFilteredColumns(COLUMN_FILTER_DIRTY_CACHE);
				if (! empty($columns)) {
					foreach ($columns as $column_name => $column) {
						$results[$column_name] = $column->getDatabaseEncodedValue();
						$column->setDirtyCacheFlag(false);
					}
				} elseif ($print) {
					Debug::print("{$f} there are no dirty cache flagged columns");
				}
				$user->setCacheValue($results);
			} elseif ($print) {
				Debug::print("{$f} cache is disabled, skipping loaded user cache");
			}
			$user->setObjectStatus(SUCCESS);
			$status = $user->loadForeignDataStructures($mysqli, false, 3);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loading foreign data structures returned error status \"{$err}\"");
				return null;
			}
			// create RecoveredAuthenticationData and set its user
			$auth_data = new RecoveredAuthenticationData();
			if ($print) {
				Debug::print("{$f} deterministic secret key has hash " . sha1($deterministicSecretKey));
			}
			$auth_data->setDeterministicSecretKey($deterministicSecretKey);
			$auth_data->handSessionToUser($user, LOGIN_TYPE_UNDEFINED);
			// delete the old one
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if (! isset($mysqli)) {
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::warning("{$f} {$err}");
				return null;
			}
			$recovery_data->setUserData($user);
			$status = $recovery_data->delete($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} deleting old session recovery data returned error status \"{$err}\"");
				return null;
			}
			// create and insert a new SessionRecoveryData
			$new_recovery_data = new SessionRecoveryData();
			app()->setUserData($user);
			$new_recovery_data->setUserData($user);
			if ($recovery_data->getBindIpAddress()) {
				$new_recovery_data->setBindIpAddress(true);
			}
			if ($recovery_data->getBindUserAgent()) {
				$new_recovery_data->setBindUserAgent(true);
			}
			$status = $new_recovery_data->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting new session recovery data returned error status \"{$err}\"");
				return null;
			}
			app()->setFlag("resumedSession", true);
			return $user;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		$cookie_secret = new Base64Datum(static::getCookieSecretColumnName());
		$cookie_secret->setNullable(true);
		$recovery_key = new ForeignKeyDatum(static::getRecoveryKeyColumnName());
		$recovery_key->setNullable(true);
		$recovery_key->setForeignDataStructureClass(SessionRecoveryData::class);
		$recovery_key->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		static::pushTemporaryColumnsStatic($columns, $cookie_secret, $recovery_key);
	}

	public function deleteSession()
	{
		$f = __METHOD__; //SessionRecoveryCookie::getShortClass()."(".static::getShortClass().")->deleteSession()";
		$recovery_key = $this->getRecoveryKey();
		$recovery_data = new SessionRecoveryData();
		$recovery_data->setSessionRecoveryCookie($this);
		$mysqli = db()->getConnection(PublicWriteCredentials::class);
		$kit = $recovery_data->unpack($mysqli, $recovery_key);
		if (! is_array($kit)) {
			$status = $recovery_data->getObjectStatus();
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} unpacking session recovery data with key \"{$recovery_key}\" returned error status \"{$err}\"");
			return null;
		}
		$recovery_data->setUserKey($kit["userKey"]);
		$recovery_data->setUserAccountType($kit['userAccountType']);
		$status = $recovery_data->loadForeignDataStructures($mysqli, false);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($this->setObjectStatus($status));
			Debug::warning("{$f} loading foreign data structures returned error status \"{$err}\"");
			return $this->getObjectStatus();
		} elseif (! $recovery_data->hasUserData()) {
			Debug::error("{$f} recovery data lacks user data");
		}
		if (user() instanceof AuthenticatedUser) {
			if (! $recovery_data->hasUserAccountType()) {
				$recovery_data->setUserAccountType(user()->getAccountType());
			}
			$status = $recovery_data->delete($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($this->setObjectStatus($status));
				Debug::warning("{$f} deleting session recovery data returned error status \"{$err}\"");
				return $this->getObjectStatus();
			}
		}
		$this->ejectColumnValue($this->getRecoveryKeyColumnName());
		$this->ejectColumnValue($this->getCookieSecretColumnName());
		return SUCCESS;
	}

	public static function getTableNameStatic(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getPhylumName(): string{
		return "sessionRecoveryCookie";
	}

	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_COOKIE;
	}

	public static function getPrettyClassName():string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getPrettyClassNames():string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function isRegistrable(): bool{
		return false;
	}

	public static function isRegistrableStatic(): bool{
		return false;
	}
}
