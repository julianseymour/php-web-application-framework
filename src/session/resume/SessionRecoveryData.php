<?php

namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\argon_hash;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\OwnerPermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\AsymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SessionRecoveryEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\JsonDatum;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use JulianSeymour\PHPWebApplicationFramework\session\BindSessionColumnsTrait;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class SessionRecoveryData extends UserFingerprint implements StaticTableNameInterface{

	use BindSessionColumnsTrait;
	use StaticTableNameTrait;
	
	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"cookieBaked"
		]);
	}

	public static function getPrettyClassName():string{
		return _("Session recovery data");
	}

	public static function getPrettyClassNames():string{
		return static::getPrettyClassName();
	}

	public static function getTableNameStatic(): string{
		return "saved_sessions";
	}

	protected function afterDeleteHook(mysqli $mysqli): int{
		$ret = parent::afterDeleteHook($mysqli);
		$cookie_class = $this->getSessionRecoveryCookieClass($this->getUserAccountType());
		$cookie = new $cookie_class();
		$cookie->unsetColumnValues();
		return $ret;
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_INSERT:
			case DIRECTIVE_DELETE:
				return new OwnerPermission($name);
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public function unpack(mysqli $mysqli, string $recovery_key){
		$f = __METHOD__;
		try{
			$print = false;
			$status = $this->loadFromKey($mysqli, $recovery_key);
			if($status !== SUCCESS) {
				if($print) {
					$err = ErrorMessage::getResultMessage($this->setObjectStatus($status));
					Debug::printStackTraceNoExit("{$f} loading session recovery data with key \"{$recovery_key}\" returned error status \"{$err}\"");
				}
				return null;
			}elseif($print) {
				Debug::print("{$f} successfully loaded session recovery data with key \'{$recovery_key}\"");
			}
			// decrypt recovery kit
			$kit = $this->getRecoveryKit();
			if(!is_array($kit)) {
				Debug::warning("{$f} recovery kit is not an array");
				Debug::print($kit);
				return null;
				Debug::printStackTrace();
			}
			if($print) {
				Debug::print("{$f} cookie superglobal contents at the bottom of SessionRecoveryData->unpack:");
				Debug::printArray($_COOKIE);
				Debug::printStackTraceNoExit();
			}
			return $kit;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getRecoveryKit(){
		$f = __METHOD__;
		$kit = $this->getColumnValue("recoveryKit");
		if(empty($kit)) {
			// Debug::warning("{$f} kit is empty");
			// Debug::printArray($_COOKIE);
			return null;
		}elseif(!is_array($kit)) {
			Debug::warning("{$f} kit is not an array");
			Debug::print($kit);
			Debug::printStackTrace();
		}
		return $this->getColumnValue("recoveryKit");
	}

	protected function afterInsertHook(mysqli $mysqli): int{
		app()->setFlag("forbidResumeSession");
		return parent::afterInsertHook($mysqli);
	}

	public static function getDataType(): string{
		return DATATYPE_SESSION_RECOVERY;
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public static function getPhylumName(): string{
		return "savedSessions";
	}

	public function setRecoveryKit($kit){
		return $this->setColumnValue("recoveryKit", $kit);
	}

	public function hasArgonHash():bool{
		return $this->hasColumnValue("argon_hash");
	}

	public function setArgonHash(string $hash):string{
		return $this->setColumnValue("argon_hash", $hash);
	}

	protected static function getSessionRecoveryCookieClass(string $type):string{
		$f = __METHOD__;
		if(empty($type)) {
			Debug::error("{$f} user account type is null or empty string");
		}elseif($type === ACCOUNT_TYPE_GUEST) {
			return GuestSessionRecoveryCookie::class;
		}
		return SessionRecoveryCookie::class;
	}

	protected function getSessionRecoveryCookie(){
		$f = __METHOD__;
		$print = false;
		if($this->hasSessionRecoveryCookie()) {
			if($print) {
				Debug::print("{$f} session recovery cookie is already defined");
			}
			return $this->getForeignDataStructure("sessionRecoveryCookie");
		}elseif($print) {
			Debug::print("{$f} session recovery cookie is undefined -- creating one now");
		}
		$type = $this->hasUserAccountType() ? $this->getUserAccountType() : "surprise";
		if($print) {
			Debug::print("{$f} user account type is \"{$type}\"");
		}
		$cookie_class = $this->getSessionRecoveryCookieClass($type);
		if($print) {
			Debug::print("{$f} about to create a new {$cookie_class}");
		}
		$cookie = new $cookie_class();
		return $this->setSessionRecoveryCookie($cookie);
	}

	public function hasSessionRecoveryCookie():bool{
		return $this->hasForeignDataStructure("sessionRecoveryCookie");
	}

	public function setSessionRecoveryCookie($struct){
		return $this->setForeignDataStructure("sessionRecoveryCookie", $struct);
	}

	public function getArgonHash():string{
		$f = __METHOD__;
		$print = false;
		if($this->hasArgonHash()) {
			if($print) {
				Debug::print("{$f} hash was already defined");
			}
			return $this->getColumnValue("argon_hash");
		}elseif($print) {
			Debug::print("{$f} now generating argon hash");
		}
		$ip = $this->getBindIpAddress() ? $_SERVER['REMOTE_ADDR'] : "";
		$user_agent = $this->getBindUserAgent() ? $_SERVER['HTTP_USER_AGENT'] : "";
		$cookie = $this->getSessionRecoveryCookie();
		if(!$cookie->hasCookieSecret()) {
			Debug::error("{$f} cookie secret is undefined");
		}
		$cookie_secret = $cookie->getCookieSecret();
		if(empty($cookie_secret)) {
			Debug::error("{$f} cookie secret is empty");
		}
		$argon_nonce = $this->getColumnValue("recoveryKitArgonNonce");
		if(empty($argon_nonce)) {
			Debug::error("{$f} argon nonce is empty");
		}
		return $this->setArgonHash(argon_hash($ip . $user_agent . $cookie_secret, $argon_nonce));
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		if(!$this->getCookieBakedFlag()) {
			$this->generateCookie();
		}
		$this->generateRecoveryKit();
		return parent::afterGenerateInitialValuesHook();
	}

	protected function generateRecoveryKit(): int{
		$f = __METHOD__;
		$user = $this->getUserData();
		$this->setColumnValue("recoveryKitArgonNonce", random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES));
		$this->setColumnValue("recoveryKitServerSecret", random_bytes(strlen($this->getArgonHash())));
		$this->setColumnValue("recoveryKitAesNonce", random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES));
		$this->setRecoveryKit([
			'deterministicSecretKey_64' => base64_encode($user->getDeterministicSecretKey(LOGIN_TYPE_FULL)),
			"userAccountType" => $user->getAccountType(),
			"userKey" => $user->getIdentifierValue()
		]);
		return SUCCESS;
	}

	public function getCookieBakedFlag(): bool{
		return $this->getFlag("cookieBaked");
	}

	public function setCookieBakedFlag(bool $value = true): bool{
		return $this->setFlag("cookieBaked");
	}

	public function generateCookie(): int{
		$f = __METHOD__;
		$print = false;
		$cookie_class = $this->getSessionRecoveryCookieClass($this->getUserAccountType());
		$session_cookie = new $cookie_class();
		if(headers_sent()) {
			Debug::error("{$f} headers have already been sent");
		}
		$session_cookie->setCookieSecret(random_bytes(128));
		$recovery_key = $this->getIdentifierValue();
		if($print) {
			Debug::print("{$f} generated key \"{$recovery_key}\"");
		}
		$session_cookie->setRecoveryKey($recovery_key);
		$this->setCookieBakedFlag(true);
		return SUCCESS;
	}

	public static function reconfigureColumnEncryption(Datum $column):void{
		switch($column->getName()){
			case "insertIpAddress":
				$column->setEncryptionScheme(AsymmetricEncryptionScheme::class);
				break;
			case "userAgent":
				$column->setEncryptionScheme(AsymmetricEncryptionScheme::class);
				break;
			default:
		}
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::declareColumns($columns, $ds);

			

			$bind_ip = new BooleanDatum("bindIpAddress");
			$bind_ip->setDefaultValue(false);
			$bind_ua = new BooleanDatum("bindUserAgent");
			$bind_ua->setDefaultValue(false);
			$recoveryKit = new JsonDatum("recoveryKit");
			$recoveryKit->setEncryptionScheme(SessionRecoveryEncryptionScheme::class);
			$argon_hash = new Base64Datum("argon_hash");
			$argon_hash->volatilize();
			// hashed userKey for user to identify their own session recovery data structures
			$hashed_key = new ForeignKeyDatum("userKeyHash");
			$hashed_key->setNullable(false);
			$hashed_key->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
			array_push($columns, $bind_ip, $bind_ua, $recoveryKit, $hashed_key, $argon_hash);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setUserKeyHash(string $hash):string{
		return $this->setColumnValue("userKeyHash", $hash);
	}

	public function hasUserKeyHash():bool{
		return $this->hasColumnValue("userKeyHash");
	}

	public function getUserKeyHash():string{
		$f = __METHOD__;
		if(!$this->hasUserKeyHash()) {
			Debug::warning("{$f} user key hash is undefined");
			return null;
		}
		return $this->getColumnValue("userKeyHash");
	}

	public function setUserData(UserData $user):UserData{
		$f = __METHOD__;
		try{
			$ret = parent::setUserData($user);
			if(!$this->hasUserKeyHash()) {
				$nonce = $user->getSessionRecoveryNonce();
				$key = $user->getIdentifierValue();
				$this->setUserKeyHash(sha1($nonce . $key));
			}
			return $ret;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$fields = [
			"updatedTimestamp",
			"reasonLogged",
			'userKey',
			"userAccountType",
			"userNameKey",
			"userTemporaryRole",
			"userDisplayName",
			"userName",
			"userNormalizedName"
		];
		foreach($fields as $field) {
			$columns[$field]->volatilize();
		}
	}
}
