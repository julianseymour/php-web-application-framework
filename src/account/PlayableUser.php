<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\push;
use function JulianSeymour\PHPWebApplicationFramework\region_code;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\correspondent\CorrespondentPermission;
use JulianSeymour\PHPWebApplicationFramework\account\correspondent\UserCorrespondence;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\auth\ReauthenticationCookie;
use JulianSeymour\PHPWebApplicationFramework\auth\ReauthenticationEvent;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationCookie;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordData;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordDatum;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordDerivedColumnsTrait;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\SecretKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoBoxPublicKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\AsymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\EnabledTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\BlobDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\FancyMultipleRadioButtons;
use JulianSeymour\PHPWebApplicationFramework\notification\NoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\push\PushSubscriptionData;
use JulianSeymour\PHPWebApplicationFramework\query\select\CountCommand;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateUser;
use Exception;
use mysqli;

abstract class PlayableUser extends UserData{

	use EnabledTrait;
	use PasswordDerivedColumnsTrait;

	protected $cacheValue;

	public abstract function getHasEverAuthenticated(): bool;

	public abstract function getHardResetCount(): int;

	public abstract function filterIpAddress(mysqli $mysqli, ?string $ip_address = null, bool $skip_insert = false): int;

	public abstract function getProfileImageData();

	public function __construct(?int $mode = ALLOCATION_MODE_EAGER){
		if($mode === ALLOCATION_MODE_SUBJECTIVE){
			$this->setAllocationMode($mode);
			$mode = ALLOCATION_MODE_EAGER;
		}
		parent::__construct($mode);
	}

	public static function declareFlags():?array{
		return array_merge(parent::declareFlags(), [
			"cached",
			"validIp"
		]);
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->cacheValue, $deallocate);
	}
	
	public function getValidIpAddressFlag():bool{
		return $this->getFlag("validIp");
	}
	
	public function setValidIpAddressFlag(bool $value=true):bool{
		return $this->setFlag("validIp", $value);
	}
	
	public function getUnambiguousName():string{
		return $this->getName();
	}

	public function getRequestEventObject(){
		$f = __METHOD__;
		try{
			if(!$this->hasRequestEventObject()){
				Debug::error("{$f} request attempt object is undefined");
			}
			return $this->getForeignDataStructure("accessAttemptKey");
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getStaticRoles(): ?array{
		$roles = parent::getStaticRoles();
		if($this->isDisabled()){
			$roles["disabled"] = "disabled";
		}else{
			$roles["enabled"] = 'enabled';
		}
		return $roles;
	}

	public function updateLastSeenTimestamp(mysqli $mysqli, int $timestamp):int{
		$f = __METHOD__;
		$print = false;
		$this->setLastSeenTimestamp($timestamp);
		return $this->update($mysqli);
	}

	public function setRequestEventObject($obj){
		return $this->setForeignDataStructure("accessAttemptKey", $obj);
	}

	public function hasRequestEventObject():bool{
		return $this->hasForeignDataStructure("accessAttemptKey");
	}

	public static function getPermissionStatic(string $name, $object){
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_INSERT:
				return new AnonymousAccountTypePermission($name);
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_PREINSERT_FOREIGN:
			case DIRECTIVE_POSTINSERT_FOREIGN:
			case DIRECTIVE_PREUPDATE_FOREIGN:
			case DIRECTIVE_POSTUPDATE_FOREIGN:
				return new SelfPermission($name);
			default:
				return parent::getPermissionStatic($name, $object);
		}
	}

	public function getLastSeenTimestampString():string{
		return getDateTimeStringFromTimestamp($this->getLastSeenTimestamp());
	}

	public function getMessageUpdateTimestamp():int{
		$f = __METHOD__;
		if(!$this->hasMessageUpdateTimestamp()){
			Debug::warning("{$f} message update timestamp is undefined");
		}
		// Debug::print("{$f} returning \"{$this->messageUpdateTimestamp}\"");
		return $this->getColumnValue("messageUpdateTimestamp");
	}

	public function hasMessageUpdateTimestamp():bool{
		return $this->hasColumnValue("messageUpdateTimestamp");
	}

	public function setMessageUpdateTimestamp(int $time):int{
		return $this->setColumnValue("messageUpdateTimestamp", $time);
	}

	public function getLastSeenTimestamp():int{
		return $this->getColumnValue("lastSeenTimestamp");
	}

	public function setLastSeenTimestamp(int $ts):int{
		return $this->setColumnValue("lastSeenTimestamp", $ts);
	}

	public function hasLastSeenTimestamp():bool{
		return $this->hasColumnValue("lastSeenTimestamp") && $this->getColumnValue("lastSeenTimestamp") > 0;
	}

	public function getNotificationDeliveryTimestamp():int{
		if($this->hasNotificationDeliveryTimestamp()){
			return $this->getColumnValue('notificationDeliveryTimestamp');
		}
		return $this->setNotificationDeliveryTimestamp($this->getInsertTimestamp());
	}

	public function setNotificationDeliveryTimestamp(int $ts):int{
		return $this->setColumnValue('notificationDeliveryTimestamp', $ts);
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasRegionCode()){
			$this->setRegionCode(region_code($_SERVER['REMOTE_ADDR']));
		}
		$status = parent::afterGenerateInitialValuesHook();
		if($print){
			Debug::print("{$f} returned from parent function");
		}
		if(!$this->hasKeyGenerationNonce()){
			$password_data = PasswordData::generate($this->getPassword());
			$this->setReceptivity(DATA_MODE_RECEPTIVE);
			$this->processPasswordData($password_data);
			deallocate($password_data);
			$this->setReceptivity(DATA_MODE_DEFAULT);
		}elseif($print){
			Debug::print("{$f} key generation nonce was already defined, probably because this was a delayed insert");
		}
		$ts = $this->generateInsertTimestamp();
		$this->setNotificationDeliveryTimestamp($ts);
		$crypto_sign_seed = $this->getColumnValue("signatureSeed");
		$length = strlen($crypto_sign_seed);
		if($length !== SODIUM_CRYPTO_SIGN_SEEDBYTES){
			$shoodbi = SODIUM_CRYPTO_SIGN_SEEDBYTES;
			Debug::error("{$f} incorrect seed length ({$length}, should be {$shoodbi}");
		}elseif($print){
			Debug::print("{$f} signature seed is the correct length");
		}
		// $this->setInsertIpAddress($_SERVER['REMOTE_ADDR']);
		return $status;
	}

	public function hasNotificationDeliveryTimestamp():bool{
		return $this->hasColumnValue("notificationDeliveryTimestamp") && $this->getColumnValue("notificationDeliveryTimestamp") > 0;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			$print = false;
			parent::declareColumns($columns, $ds);
			$hash = new PasswordDatum("password");
			$hash->setUserWritableFlag(true);
			$hash->setNeverLeaveServer(true);
			$hash->setHumanReadableName(_("Password"));
			$privateKey = new BlobDatum("privateKey");
			$privateKey->setUserWritableFlag(true);
			$privateKey->setNullable(false);
			$privateKey->setNeverLeaveServer(true);
			$privateKey->setEncryptionScheme(SymmetricEncryptionScheme::class);
			$privateKey->setTranscryptionKeyName("deterministicSecretKey");
			$public = new SodiumCryptoBoxPublicKeyDatum("publicKey");
			$public->setUserWritableFlag(true);
			$public->setNullable(false);
			$public->setNeverLeaveServer(true);
			$keyGenerationNonce = new Base64Datum("keyGenerationNonce");
			$keyGenerationNonce->setUserWritableFlag(true);
			$keyGenerationNonce->setNeverLeaveServer(true);

			$signaturePrivateKey = new BlobDatum("signaturePrivateKey");
			$signaturePrivateKey->setNullable(false);
			$signaturePrivateKey->setUserWritableFlag(true);
			$signaturePrivateKey->setNeverLeaveServer(true);
			$signaturePrivateKey->setEncryptionScheme(AsymmetricEncryptionScheme::class);
			$signaturePrivateKey->setRequiredLength(SODIUM_CRYPTO_SIGN_SECRETKEYBYTES);

			$signatureSeed = new BlobDatum("signatureSeed");
			$signatureSeed->setNullable(false);
			$signatureSeed->setUserWritableFlag(true);
			$signatureSeed->setNeverLeaveServer(true);
			$signatureSeed->setEncryptionScheme(AsymmetricEncryptionScheme::class);
			$signatureSeed->setRequiredLength(SODIUM_CRYPTO_SIGN_SEEDBYTES);

			$public_sign = new Base64Datum("signaturePublicKey");
			$public_sign->setNeverLeaveServer(true);
			$public_sign->setRequiredLength(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
			$notify_ts = new TimestampDatum("notificationDeliveryTimestamp");
			$notify_ts->setHumanReadableName(_("Notification delivery timestamp"));
			$notify_ts->setElementClass(HiddenInput::class);
			$seen = new TimestampDatum("lastSeenTimestamp");
			$seen->setUserWritableFlag(true);
			$seen->setSensitiveFlag(true);
			$seen->setDefaultValue(0);
			$enabled = static::getIsEnabledDatum(true);
			$encrypted_nonce = new NonceDatum("sessionRecoveryNonce"); // used to hash the copy of the user key stored in the session recovery data, so that only the owner knows which recovery data belong to them
			$encrypted_nonce->setEncryptionScheme(AsymmetricEncryptionScheme::class);

			if($ds instanceof DataStructure && $ds->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
				$dsk = new VirtualDatum("deterministicSecretKey");
			}else{
				$dsk = new SecretKeyDatum("deterministicSecretKey");
				$dsk->volatilize();
			}

			$unambiguousName = new VirtualDatum("unambiguousName");

			$onlineStatus = new StringEnumeratedDatum("onlineStatus");
			$onlineStatus->setValidEnumerationMap([
				ONLINE_STATUS_ONLINE,
				ONLINE_STATUS_APPEAR_OFFLINE,
				ONLINE_STATUS_AWAY,
				ONLINE_STATUS_BUSY,
				ONLINE_STATUS_CUSTOM,
				ONLINE_STATUS_NONE
			]);
			$onlineStatus->setValue(ONLINE_STATUS_NONE);
			$onlineStatus->setHumanReadableName(_("Online status"));
			$onlineStatus->setElementClass(FancyMultipleRadioButtons::class);
			$onlineStatus->setDefaultValue(ONLINE_STATUS_NONE);
			
			$customOnlineStatusString = new TextDatum("customOnlineStatusString");
			$customOnlineStatusString->setNullable(true);
			$customOnlineStatusString->setHumanReadableName(_("Custom status string"));

			$pushed = [
				$hash,
				$privateKey,
				$public,
				$keyGenerationNonce,
				$signaturePrivateKey,
				$public_sign,
				$notify_ts,
				$seen,
				$enabled,
				$encrypted_nonce,
				$dsk,
				$signatureSeed,
				$unambiguousName,
				$onlineStatus,
				$customOnlineStatusString
			];
			if($ds instanceof DataStructure && !$ds instanceof TemplateUser && $ds->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
				$unreadNotificationCount = new UnsignedIntegerDatum("unreadNotificationCount", 64);
				$unreadNotificationCount->setDefaultValue(0);
				$count = new CountCommand("*");
				$unreadNotificationCount->setSubqueryExpression($count);
				$unreadNotificationCount->setSubqueryClass(RetrospectiveNotificationData::class);
				$unreadNotificationCount->setSubqueryParameters([
					"userKey",
					NOTIFICATION_STATE_UNREAD
				]);
				$unreadNotificationCount->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
				$dsc = get_class($ds);
				$lazy = RetrospectiveNotificationData::generateLazyAliasExpression($dsc);
				$and = new AndCommand(
					new WhereCondition(
						"notifications_alias.uniqueKey",
						OPERATOR_IN,
						null,
						$lazy
					),
					new WhereCondition("notificationState", OPERATOR_EQUALS)
				); // 's')
				$unreadNotificationCount->setSubqueryWhereCondition($and);
				$accessAttemptKey = new ForeignKeyDatum("accessAttemptyKey", RELATIONSHIP_TYPE_ONE_TO_ONE);
				$accessAttemptKey->setConverseRelationshipKeyName("userKey");
				$accessAttemptKey->volatilize();
				array_push($pushed, $unreadNotificationCount, $accessAttemptKey);
			}
			array_push($columns, ...$pushed);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getSessionRecoveryNonce(){
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->hasColumnValue("sessionRecoveryNonceCipher")){
				if($print){
					Debug::print("{$f} session recovery nonce is undefined -- creating one now");
				}
				$nonce = random_bytes(32);
				$back = $this->getReceptivity();
				$this->setReceptivity(DATA_MODE_RECEPTIVE);
				$this->setSessionRecoveryNonce($nonce);
				$this->getColumn("sessionRecoveryNonce")->setUpdateFlag(true);
				$mysqli = db()->getConnection(PublicWriteCredentials::class);
				$status = $this->update($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} writing session recovery nonce returned error status \"{$err}\"");
				}elseif($print){
					Debug::print("{$f} successfully wrote session recovery nonce");
				}
				$this->setReceptivity($back);
			}elseif($print){
				Debug::print("{$f} already have a session recovery nonce cipher");
			}
			return $this->getColumnValue("sessionRecoveryNonce");
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getNormalizedDisplayName():?string{
		$f = __METHOD__;
		try{
			$sdn = $this->getColumnValue('displayNormalizedName');
			if(isset($sdn)){
				return $sdn;
			}
			$dn = $this->getDisplayName();
			if(isset($dn)){
				$sdn = NameDatum::normalize($dn);
				return $this->setColumnValue('displayNormalizedName', $sdn);
			}
			return null;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasDisplayName():bool{
		return $this->hasColumn("displayName") && $this->hasColumnValue("displayName");
	}

	public function getDisplayName():string{
		if(!$this->hasDisplayName()){
			return $this->getName();
		}
		return $this->getColumnValue('displayName');
	}

	public function setDisplayName(string $name):string{
		return $this->setColumnValue('displayName', $name);
	}

	public function authenticate(mysqli $mysqli, int $mode = LOGIN_TYPE_FULL): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if(!isset($mysqli)){
				Debug::error("{$f} mysqli object is undefined");
			}elseif($print){
				Debug::print("{$f} about to get reauthentication key");
			}
			$session = static::createAuthenticationData($mode);
			if(!$session->hasSignature()){
				if($print){
					$sdc = $session->getClass();
					Debug::print("{$f} session data of class {$sdc} lacks a signature");
				}
				$session->unsetColumnValues();
				deallocate($session);
				return FAILURE;
			}
			$reauth_nonce = $session->getReauthenticationNonce();
			switch($mode){
				case LOGIN_TYPE_PARTIAL:
					$cookie = new PreMultifactorAuthenticationCookie();
					break;
				case LOGIN_TYPE_FULL:
					$cookie = new ReauthenticationCookie();
					break;
				default:
					Debug::error("{$f} invalid login type \"{$mode}\"");
			}
			if(!$cookie->hasReauthenticationKey()){
				Debug::warning("{$f} reauthentication key is null");
				deallocate($session);
				deallocate($cookie);
				return FAILURE;
			}elseif($print){
				Debug::print("{$f} reauthentication key is defined");
			}
			$reauth_key = $cookie->getReauthenticationKey(); // LOGIN_TYPE_FULL);
			deallocate($cookie);
			if(!$session->hasReauthenticationHash()){
				Debug::warning("{$f} reauthentication hash is not set in session memory");
				deallocate($session);
				return FAILURE;
			}
			$reauth_hash = $session->getReauthenticationHash();
			if(!isset($reauth_hash)){
				if($print){
					Debug::print("{$f} reautentication hash undefined; user is not fully logged in");
				}
				deallocate($session);
				return FAILURE;
			}elseif($print){
				Debug::print("{$f} reauthentication hash is defined; about to verify it");
			}
			$ret = password_verify($reauth_nonce.$reauth_key, $reauth_hash);
			sodium_memzero($reauth_key);
			$reauth = new ReauthenticationEvent();
			$reauth->setUserData($this);
			if(!$ret){
				Debug::warning("{$f} reauthentication failed");
				$reauth->setLoginResult(ERROR_LOGIN_CREDENTIALS);
				$reauth->setLoginSuccessful(FAILURE);
			}else{
				if($print){
					Debug::print("{$f} password hash validated; about to validate signature");
				}
				$signature = $session->getSignature();
				$ret = $this->verifySignedMessage($signature, $reauth_hash);
				if(!$ret){
					Debug::warning("{$f} signature verification failed");
					$reauth->setLoginResult(ERROR_LOGIN_CREDENTIALS);
					$reauth->setLoginSuccessful(FAILURE);
				}else{
					if($print){
						Debug::print("{$f} signature validated");
					}
					$reauth->setLoginResult(SUCCESS);
					$reauth->setLoginSuccessful(SUCCESS);
				}
			}
			if($this->hasRequestEventObject()){
				$this->releaseForeignDataStructure('accessAttemptKey', true);
			}
			$this->setRequestEventObject($reauth);
			if($print){
				Debug::print("{$f} about to validate current IP address");
			}
			if($this->getValidIpAddressFlag()){
				if($print){
					$did = $this->getDebugId();
					$decl = $this->getDeclarationLine();
					Debug::print("{$f} IP address has already been validated for object with debug ID {$did} declared {$decl}");
				}
				$valid = SUCCESS;
			}else{
				if($print){
					$did = $this->getDebugId();
					$decl = $this->getDeclarationLine();
					Debug::print("{$f} current IP address has not yet been validated. This object has a debug ID {$did} and was declared {$decl}");
				}
				$valid = $this->validateCurrentIpAddress($mysqli);
				if($print){
					Debug::print("{$f} returned from validateCurrentIpAddress");
				}
			}
			if(!is_int($valid)){
				Debug::error("{$f} filterIpAddress returned null");
			}elseif($valid !== SUCCESS){
				$err = ErrorMessage::getResultMessage($valid);
				Debug::warning("{$f} validate current ip address returned error status \"{$err}\"");
				$this->setObjectStatus(ERROR_BLOCKED_IP_ADDRESS);
				$session->unsetColumnValues();
				deallocate($session);
				return FAILURE;
			}elseif(!$ret){
				if($print){
					Debug::print("{$f} reauthentication failed");
				}
				$session->unsetColumnValues();
				deallocate($session);
				return FAILURE;
			}elseif($print){
				Debug::print("{$f} returning normally");
			}
			deallocate($session);
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function validateCurrentIpAddress(mysqli $mysqli):int{
		$f = __METHOD__;
		$print = false;
		$ret = $this->filterIpAddress($mysqli, $_SERVER['REMOTE_ADDR'], false);
		if($ret === SUCCESS){
			if($print){
				Debug::print("{$f} successfully validated durrent IP address");
			}
			$this->setValidIpAddressFlag(true);
		}elseif($print){
			Debug::print("{$f} current IP address was rejected");
		}
		return $ret;
	}
	
	protected function generateReauthenticationHash($mode = LOGIN_TYPE_UNDEFINED, $old_mode = LOGIN_TYPE_UNDEFINED){
		$f = __METHOD__;
		try{
			$sd = static::createAuthenticationData($mode);
			$cookie = new ReauthenticationCookie();
			$reauth_key = $cookie->generateReauthenticationKey();
			$reauth_nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
			$hash = $sd->generateReauthenticationHash($reauth_nonce, $reauth_key);
			$sd->setSignature($this->signMessage($hash));
			deallocate($sd);
			return $hash;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getUserHardResetCount(){
		return $this->getHardResetCount();
	}

	protected static function createAuthenticationData($mode = LOGIN_TYPE_UNDEFINED){
		$f = __METHOD__;
		try{
			switch($mode){
				case LOGIN_TYPE_PARTIAL:
					return new PreMultifactorAuthenticationData();
				case LOGIN_TYPE_FULL:
					return new FullAuthenticationData();
				default:
					Debug::error("{$f} illegal login type \"{$mode}\"");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * alternate method of acquiring a deterministic seret key that is already in session variables; needed because users with MFA have to login with 2 steps and the password is only posted for the first one
	 *
	 * @param int $mode
	 * @return NULL|string
	 */
	public function getDeterministicSecretKey($mode = LOGIN_TYPE_UNDEFINED):string{
		$f = __METHOD__;
		try{
			$print = false;
			if($mode === LOGIN_TYPE_UNDEFINED){
				Debug::error("{$f} invalid login type \"{$mode}\"");
			}
			$sd = static::createAuthenticationData($mode);
			if(is_string($sd)){
				Debug::error("{$f} authentication data is the string \"{$sd}\"");
			}elseif($sd->hasDeterministicSecretKey() && $print){
				Debug::print("{$f} session data already has its deterministic secret key");
			}
			$key = $sd->getDeterministicSecretKey();
			deallocate($sd);
			if($print){
				$hash = sha1($key);
				$length = strlen($key);
				Debug::print("{$f} returning key of length {$length} with hash \"{$hash}\"");
			}
			return $key;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setDeterministicSecretKey(string $dsk, int $mode = LOGIN_TYPE_UNDEFINED):string{
		$f = __METHOD__;
		$print = false;
		if($this->getColumn("deterministicSecretKey") instanceof VirtualDatum){
			if($print){
				Debug::print("{$f} deterministic secret key column is virtual -- setting value in authentication data");
			}
			$auth = static::createAuthenticationData($mode);
			$auth->setDeterministicSecretKey($dsk);
			deallocate($auth);
			return $dsk;
		}elseif($print){
			Debug::print("{$f} deterministic secret ket column is not virtual");
		}
		return $this->setColumnValue("deterministicSecretKey", $dsk);
	}

	/**
	 * note: calling this function will destroy the deterministic secret key 100% of the time,
	 *
	 * @param array $password_data
	 * @return string
	 */
	public function processPasswordData(PasswordData $password_data):int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered; about to set deterministic secret key");
			}

			$this->setDeterministicSecretKey($password_data->getDeterministicSecretKey(), LOGIN_TYPE_FULL);
			$this->setPrivateKey($password_data->getPrivateKey());
			$this->setKeyGenerationNonce($password_data->getKeyGenerationNonce());
			if(!$this->hasKeyGenerationNonce()){
				Debug::error("{$f} key generation nonce is undefined immediately after setting it");
			}
			$this->setPasswordHash($password_data->getPasswordHash());
			$this->setPublicKey($password_data->getPublicKey());
			$this->setSignaturePublicKey($password_data->getSignaturePublicKey());
			$this->setSignaturePrivateKey($password_data->getSignaturePrivateKey());
			if($this->hasColumn("sessionRecoveryNonce")){
				$this->setSessionRecoveryNonce($password_data->getSessionRecoveryNonce());
			}elseif($print){
				Debug::print("{$f} this object does not have a session recovery nonce");
			}
			$this->setSignatureSeed($password_data->getSignatureSeed());
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getNotifications():array{
		return $this->getForeignDataStructureList(NotificationData::getPhylumName());
	}

	public function getNotificationCount():int{
		return $this->getForeignDataStructureCount(NotificationData::getPhylumName());
	}

	/**
	 *
	 * @param NotificationData $note
	 * @return int
	 */
	public function transmitPushNotification(mysqli $mysqli, NotificationData $note){
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				Debug::print("{$f} user ".$this->getUnambiguousName());
			}
			if(!$note instanceof NotificationData){
				Debug::error("{$f} second parameter should be a notification data structure");
			}elseif(!isset($mysqli)){
				Debug::error("{$f} mysqli object is undefined");
			}
			$arr = Loadout::loadChildClass(
				$mysqli, 
				$this, 
				PushSubscriptionData::getPhylumName(), 
				PushSubscriptionData::class, 
				PushSubscriptionData::selectStatic()->where(
					PushSubscriptionData::whereIntersectionalHostKey(static::class, "userKey")
				)->withParameters($this->getIdentifierValue(), "userKey")->withTypeSpecifier("ss")
			);
			if(empty($arr)){
				if($print){
					Debug::print("{$f} no push subscriptions saved");
				}
				return SUCCESS;
			}
			$count = count($arr);
			if($print){
				Debug::print("{$f} counted {$count} push subscriptions");
			}
			$deliverable = $note->getPushNotificationDeliverable();
			if($this instanceof AnonymousUser){
				if($print){
					Debug::print("{$f} skipping signature verification for anonymous user; about to send push note");
				}
				foreach($arr as $sub){
					$sub->sendPushNotification($deliverable);
				}
			} else
				foreach($arr as $sub){
					if(hasInputParameter('pushSubscriptionKey')){
						$subscription_key = getInputParameter('pushSubscriptionKey');
						if($print){
							Debug::print("{$f} posted push subscription key is \"{$subscription_key}\"");
						}
						if($sub->getIdentifierValue() == $subscription_key){
							if($print){
								Debug::print("{$f} no need to send a push notification to this subscription, it's the one that initiated the request");
							}
							continue;
						}
					}
					
					if(!$sub->hasUserData()){
						$key = $sub->getIdentifierValue();
						$did = $sub->getDebugId();
						$decl = $sub->getDeclarationLine();
						Debug::error("{$f} push subscription with key {$key} and debug ID {$did} declared {$decl} lacks a user data");
					}
					if($print){
						$username = $this->getName();
						Debug::print("{$f} about to send {$username} a push notification");
					}
					$sub->sendPushNotification($deliverable);
				}
			if($print){
				Debug::print("{$f} returning normally");
			}
			return $this->setObjectStatus(SUCCESS);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function updateExistingNotification(mysqli $mysqli, NoteworthyInterface $subject):int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				Debug::print("{$f} user ".$this->getUnambiguousName());
			}
			if($mysqli == null){
				Debug::error("{$f} mysqli connection failed");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}elseif($subject instanceof UserOwned){
				if($print){
					Debug::print("{$f} subject is a ".$subject->getShortClass().", which is owned by someone");
				}
				if(!$subject->hasUserTemporaryRole()){
					Debug::error("{$f} subject lacks a user role");
				}
				if($subject instanceof UserCorrespondence){
					if($print){
						Debug::print("{$f} subject is user correspondence");
					}
					if(!$subject->hasCorrespondentKey()){
						if(!$subject->getColumn("correspondentKey")->isNullable()){
							Debug::error("{$f} correspondent key is non-nullable and undefined");
							return $this->setObjectStatus(ERROR_NULL_CORRESPONDENT_KEY);
						}elseif($print){
							Debug::print("{$f} correspondent key is nullable");
						}
					}elseif($print){
						Debug::print("{$f} correspondent key is defined");
					}
				}elseif($print){
					Debug::print("{$f} subject is not a UserCorrespondence");
				}
				$role = $subject->getUserTemporaryRole();
				switch($role){
					case USER_ROLE_SENDER:
						$state = NOTIFICATION_STATE_READ;
						break;
					case USER_ROLE_RECIPIENT:
						$state = NOTIFICATION_STATE_UNREAD;
						break;
					default:
						Debug::error("{$f} unsupported user role \"{$role}\"");
				}
			}else{
				$state = NOTIFICATION_STATE_READ;
			}
			$note_class = $subject->getNotificationClass();
			$note_type = $note_class::getNotificationTypeStatic();
			$select = RetrospectiveNotificationData::selectStatic()->where(
				new AndCommand(
					RetrospectiveNotificationData::whereIntersectionalHostKey(
						mods()->getUserClass($this->getAccountType()), 
						"userKey"
					), 
					new WhereCondition("subtype", OPERATOR_EQUALS)
				)
			)->withTypeSpecifier('sss')->withParameters([
				$this->getIdentifierValue(),
				"userKey",
				$note_type
			]);
			if($this->hasCorrespondentObject()){
				$select->pushWhereConditionParameters(
					RetrospectiveNotificationData::whereIntersectionalHostKey(
						mods()->getUserClass(
							$this->getCorrespondentObject()->getAccountType()
						), 
						"correspondentKey"
					)
				);
				$select->pushParameters($this->getCorrespondentKey(), "correspondentKey");
				$select->appendTypeSpecifier('ss');
			}elseif($print){
				Debug::print("{$f} correspondent data is undefined");
			}
			if($print){
				Debug::print("{$f} select statement is \"{$select}\" with the following parameters:");
				Debug::printArray($select->getParameters());
			}
			$result = $select->executeGetResult($mysqli);
			$count = $result->num_rows;
			if($count === 0){
				if($print){
					Debug::print("{$f} there was no notification for this correspondent previously");
				}
				$result->free_result();
				return $this->notify($mysqli, $subject);
			}elseif($count > 1){
				Debug::error("{$f} more than one result for this correspondent -- this function is for updating notification types that have a maximum of one per correspondent, which is not the case for {$note_class}");
			}elseif($print){
				Debug::print("{$f} user already has a notification for this correspondent");
			}
			$old_notification = new $note_class();
			$results = $result->fetch_all(MYSQLI_ASSOC);
			$result->free_result();
			$old_notification->processQueryResultArray($mysqli, $results[0]);
			unset($results);
			$old_notification->loadIntersectionTableKeys($mysqli);
			$old_notification->setObjectStatus(SUCCESS);
			$old_notification->setReceptivity(DATA_MODE_RECEPTIVE);
			$old_notification->setUserData($this);
			$old_type = $old_notification->getSubjectDataType();
			$old_subtype = $old_notification->getSubjectSubtype();
			$old_notification->setSubjectData($subject);
			$new_type = $old_notification->getSubjectDataType();
			$new_subtype = $old_notification->getSubjectSubType();
			if($old_type !== $new_type){
				if($print){
					Debug::print("{$f} subject type changed from {$old_type} to {$new_type}; subtype changed from {$old_subtype} to {$new_subtype}");
				}
				if(!$old_notification->getColumn("subjectDataType")->getUpdateFlag()){
					Debug::error("{$f} subjectDataType should be flagged for update");
				}
			}
			$old_notification->setNotificationState($state);
			$new_count = 0;
			if($state === NOTIFICATION_STATE_UNREAD){
				$new_count = $old_notification->getNotificationCount() + 1;
				if($note_type === NOTIFICATION_TYPE_TEST){
					$permission = SUCCESS;
				}else{
					$permission = new CorrespondentPermission(DIRECTIVE_UPDATE);
				}
				$old_notification->setPermission(DIRECTIVE_UPDATE, $permission);
			}
			$old_notification->setNotificationCount($new_count);
			//$subject->setForeignDataStructure('notificationKey', $old_notification);
			$status = $old_notification->update($mysqli);
			$old_notification->setReceptivity(DATA_MODE_DEFAULT);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} updating the old notification returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($note_type === NOTIFICATION_TYPE_TEST || $this->getPushNotificationStatus($note_type)){
				if($print){
					Debug::print("{$f} enqueueing push notification");
				}
				push()->enqueue($old_notification);
			}
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function afterInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			$status = parent::afterInsertHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} parent function executed successfully");
			}
			if(!$this instanceof Administrator){
				$status = config()->afterAccountCreationHook($mysqli, $this); // $this->writeIntroductoryMessageNote($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} afterAccountCreationHook returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} returning normally");
				}
			}elseif($print){
				Debug::print("{$f} this is an administrator, skipping acocunt creation hook");
			}
			return $this->setObjectStatus($status);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getVirtualColumnValue(string $columnName){
		switch($columnName){
			case "deterministicSecretKey":
				return $this->getDeterministicSecretKey(LOGIN_TYPE_FULL);
			case "emailTestNotifications":
			case "pushTestNotifications":
				return true;
			case "unambiguousName":
				return $this->getUnambiguousName();
			default:
				return parent::getVirtualColumnValue($columnName);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		$f = __METHOD__;
		try{
			switch($column_name){
				case "deterministicSecretKey":
					$session = $this->createAuthenticationData(LOGIN_TYPE_FULL);
					$ret = $session->hasDeterministicSecretKey();
					deallocate($session);
					return $ret;
				case "emailTestNotifications":
				case "pushTestNotifications":
					return true;
				case "unambiguousName":
					return $this->hasName();
				default:
					return parent::hasVirtualColumnValue($column_name);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param NoteworthyInterface $subject
	 * @return int
	 */
	public final function notifyIfWarranted(mysqli $mysqli, NoteworthyInterface $subject): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} user ".$this->getUnambiguousName());
			}
			if($mysqli == null){
				Debug::error("{$f} database connection failed");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}elseif($subject == null){
				Debug::error("{$f} received null parameter");
				return $this->setObjectStatus(ERROR_NULL_OBJECT);
			}elseif(!$subject->isNotificationDataWarranted($this)){
				if($print){
					Debug::print("{$f} notification data is unwarranted");
				}
				return $this->setObjectStatus(SUCCESS);
			}elseif($print){
				Debug::print("{$f} notification data is warranted");
			}
			$class = $subject->getNotificationClass();
			if(!class_exists($class)){
				Debug::error("{$f} class \"{$class}\" does not exist");
			}
			$mode = $class::getNotificationUpdateMode();
			switch($mode){
				case NOTIFICATION_MODE_UPDATE_EXISTING:
					if($print){
						Debug::print("{$f} will attempt to update an existing notification");
					}
					return $this->updateExistingNotification($mysqli, $subject);
				case NOTIFICATION_MODE_SEND_NEW:
					return $this->notify($mysqli, $subject);
				default:
					Debug::error("{$f} invalid notification update mode");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public final function notify(mysqli $mysqli, NoteworthyInterface $subject): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} user ".$this->getUnambiguousName());
			}
			$subject_key = $subject->getIdentifierValue();
			$username = $this->getName();
			if($print){
				$cn = $subject->getClass();
				Debug::print("{$f} about to send a notification for {$cn} with key {$subject_key} to user {$username}");
			}
			// create notification object
			$note_class = $subject->getNotificationClass();
			$n = new $note_class();
			$n->setUserData($this);
			if($this->hasCorrespondentObject()){
				$n->setCorrespondentObject($this->getCorrespondentObject());
			}
			$n->setSubjectData($subject);
			$status = $n->send($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} sending notification data returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return $this->setObjectStatus($status);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function validatePrivateKey($where): void{
		$f = __METHOD__;
		try{
			$class = static::class;
			$key = $this->getIdentifierValue();
			Debug::print("{$f} validating key for {$class} with key \"{$key}\" {$where}");
			if($this->getColumn("privateKeyCipher")->hasValue()){
				Debug::print("{$f} private key cipher is defined");
				$this->getColumn("privateKey")->ejectValue();
				if($this->getColumn("privateKeyCipher")->hasValue()){
					Debug::print("{$f} private key cipher is still defined after excising private key");
				}else{
					Debug::print("{$f} private key cipher is undefined after excising private key");
				}
			}else{
				Debug::print("{$f} private key cipher is not defined");
			}
			$pk = $this->getPrivateKey();
			if(empty($pk)){
				Debug::error("{$f} private key returned null or empty string");
			}else{
				Debug::print("{$f} private key hash is \"" . sha1($pk) . "\"");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getArrayMembershipConfiguration($config_id): array{
		$f = __METHOD__;
		$print = false;
		$config = parent::getArrayMembershipConfiguration($config_id);
		switch($config_id){
			case CONST_DEFAULT:
			default:
				if($print){
					if($this->hasName()){
						Debug::print("{$f} this user has a name and thus has an unambiguous name");
					}else{
						$decl = $this->getDeclarationLine();
						Debug::warning("{$f} this user does NOT have an unambiguous name; instantiated {$decl}");
					}
				}
				$config['unambiguousName'] = $this->hasName();
		}
		return $config;
	}

	public function hasCacheValue(): bool{
		return isset($this->cacheValue) && is_array($this->cacheValue) && !empty($this->cacheValue);
	}

	public function setCacheValue(array $value): array{
		if($this->hasCacheValue()){
			$this->release($this->cacheValue);
		}
		return $this->cacheValue = $this->claim($value);
	}

	public function getCacheValue(): array{
		$f = __METHOD__;
		if(!$this->hasCacheValue()){
			Debug::error("{$f} cache value is undefined");
		}
		return $this->cacheValue;
	}

	protected function nullPrivateKeyHook(): int{
		$f = __METHOD__;
		Debug::error("{$f} private key is null");
		return FAILURE;
	}
	
	public function getVisibleOnlineStatus(PlayableUser $viewer){
		$f = __METHOD__;
		$print = false;
		$status = $this->getOnlineStatus();
		if($status === ONLINE_STATUS_NONE && ! $viewer instanceof Administrator){
			if($print){
				Debug::print("{$f} user does not share their messenger status, and the person asking is not the admin");
			}
			return $status;
		}
		$time = time();
		$last_seen = $this->getLastSeenTimestamp();
		if($time - $last_seen >= SESSION_TIMEOUT_SECONDS){
			if($print){
				Debug::print("{$f} last seen timestamp {$last_seen} is too long ago compared to present ({$time}) -- user is offline");
			}
			return ONLINE_STATUS_OFFLINE;
		}elseif($this->hasColumn("logoutTimestamp") && $this->hasLogoutTimestamp()){
			$logout = $this->getLogoutTimestamp();
			if($logout >= $last_seen){
				if($print){
					Debug::print("{$f} logout timestamp ({$logout}) is more recent than last seen timestamp ({$last_seen})");
				}
				return ONLINE_STATUS_OFFLINE;
			}elseif($print){
				Debug::print("{$f} logout timestamp ({$logout}) is older than last seen timestamp ({$last_seen})");
			}
		}elseif($print){
			Debug::print("{$f} user does not have a logout timestamp");
		}
		if($status === ONLINE_STATUS_APPEAR_OFFLINE && ! $viewer instanceof Administrator){
			if($print){
				Debug::print("{$f} user is online but wants to appear offline, and the person asking is not admin");
			}
			return ONLINE_STATUS_OFFLINE;
		}elseif($print){
			Debug::print("{$f} returning \"{$status}\"");
		}
		return $status;
	}
	
	public static function getOnlineStatusStringStatic($status){
		$f = __METHOD__;
		try{
			switch($status){
				case ONLINE_STATUS_UNDEFINED:
					return _("Undefined");
				case ONLINE_STATUS_ONLINE:
					return _("Online");
				case ONLINE_STATUS_APPEAR_OFFLINE:
					return _("Appear offline");
				case ONLINE_STATUS_AWAY:
					return _("Away");
				case ONLINE_STATUS_BUSY:
					return _("Busy");
				case ONLINE_STATUS_CUSTOM:
					return _("Custom");
				case ONLINE_STATUS_NONE:
					return _("None");
				default:
					return _("Error");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getCustomOnlineStatusString(){
		return $this->getColumnValue("customOnlineStatusString");
	}
	
	public function setCustomOnlineStatusString($string){
		return $this->setColumnValue("customOnlineStatusString", $string);
	}
	
	public function hasCustomOnlineStatusString(){
		return $this->hasColumnValue("customOnlineStatusString");
	}
	
	public function ejectCustomOnlineStatusString(){
		return $this->ejectColumnValue("customOnlineStatusString");
	}
	
	public function getOnlineStatus(){
		return $this->getColumnValue("onlineStatus");
	}
	
	public function setOnlineStatus($status){
		return $this->setColumnValue("onlineStatus", $status);
	}
	
	public function getOnlineStatusString(){
		$status = $this->getOnlineStatus();
		return $status === ONLINE_STATUS_CUSTOM ? $this->getCustomOnlineStatusString() : static::getOnlineStatusStringStatic($status);
	}
}
