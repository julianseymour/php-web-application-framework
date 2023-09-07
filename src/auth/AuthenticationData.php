<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth;

use function JulianSeymour\PHPWebApplicationFramework\argon_hash;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\getlocale;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\UserMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoSignatureDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use Exception;
use mysqli;

abstract class AuthenticationData extends DataStructure{

	public abstract static function getAuthenticationType();

	public abstract static function getReauthenticationHashColumnName():string;

	public abstract static function getReauthenticationNonceColumnName():string;

	public abstract static function getDeterministicSecretKeyColumnName():string;

	public abstract static function getSignatureColumnName():string;

	public abstract static function getUsernameColumnName():string;
	
	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_SESSION;
	}

	public function __construct(){
		$f = __METHOD__;
		parent::__construct();
		if (! $this->hasColumn(static::getUserKeyColumnName())) {
			Debug::error("{$f} user key is undefined");
		}
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_DELETE:
				return SUCCESS;
			default:
				return FAILURE;
		}
	}

	protected static final function getUserKeyColumnName():string{
		return static::getUserMetadataBundleName() . "Key";
	}

	protected static final function getUserAccountTypeColumnName():string{
		return static::getUserMetadataBundleName() . "AccountType";
	}

	public function setUserAccountType(string $value):string{
		return $this->setColumnValue(static::getUserAccountTypeColumnName(), $value);
	}

	public function hasUserAccountType():bool{
		return $this->hasColumnValue(static::getUserAccountTypeColumnName());
	}

	public function getUserAccountType():string{
		$f = __METHOD__;
		if (! $this->hasUserAccountType()) {
			Debug::error("{$f} user account type is undefined");
		}
		return $this->getColumnValue(static::getUserAccountTypeColumnName());
	}

	public function setReauthenticationNonce(string $nonce):string{
		return $this->setColumnValue(static::getReauthenticationNonceColumnName(), $nonce);
	}

	public function hasUserData():bool{
		return $this->hasForeignDataStructure(static::getUserKeyColumnName());
	}

	public function getUserData():PlayableUser{
		$f = __METHOD__;
		if (! $this->hasUserData()) {
			Debug::error("{$f} user object is undefined");
		}
		return $this->getForeignDataStructure(static::getUserKeyColumnName());
	}

	public function getReauthenticationNonce():string{
		return $this->getColumnValue(static::getReauthenticationNonceColumnName());
	}

	public function getReauthenticationHash():string{
		return $this->getColumnValue(static::getReauthenticationHashColumnName());
	}

	public function hasReauthenticationHash():bool{
		return $this->getColumnValue(static::getReauthenticationHashColumnName());
	}

	public function ejectReauthenticationHash():string{
		return $this->ejectColumnValue(static::getReauthenticationHashColumnName());
	}

	public function setReauthenticationHash(string $value):string{
		return $this->setColumnValue(static::getReauthenticationHashColumnName(), $value);
	}

	public function hasReauthenticationNonce():bool{
		return $this->hasColumnValue(static::getReauthenticationNonceColumnName());
	}

	public function ejectReauthenticationNonce():string{
		return $this->ejectColumnValue(static::getReauthenticationNonceColumnName());
	}

	public function hasUsername():bool{
		return $this->hasColumnValue(static::getUsernameColumnName());
	}

	public function isRegistrable(): bool{
		return false;
	}

	public static function isRegistrableStatic(): bool{
		return false;
	}

	protected static function getUserMetadataBundleName():string{
		return "user";
	}

	public function getVirtualColumnValue(string $columnName){
		switch ($columnName) {
			case static::getUserAccountTypeColumnName() . "String":
				return UserData::getAccountTypeStringStatic($this->getUserAccountType());
			case static::getUserMetadataBundleName() . "DisplayName":
				return $this->getUserData()->getDisplayName();
			default:
				return parent::getVirtualColumnValue($columnName);
		}
	}

	public function hasVirtualColumnValue(string $columnName): bool{
		switch ($columnName) {
			case static::getUserAccountTypeColumnName() . "String":
				return $this->hasUserAccountType();
			case static::getUserMetadataBundleName() . "DisplayName":
				return $this->hasUserData() && $this->getUserData()->hasDisplayName();
			default:
				return parent::hasVirtualColumnValue($columnName);
		}
	}

	public function acquireUserData(mysqli $mysqli):PlayableUser{
		if ($this->hasUserData()) {
			return $this->getUserData();
		}
		return $this->setUserData(mods()->getUserClass($this->getUserAccountType())::getObjectFromKey($mysqli, $this->getUserKey()));
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			$name = new TextDatum(static::getUsernameColumnName());
			$key = new UserMetadataBundle(static::getUserMetadataBundleName(), $ds);
			$dsk = new Base64Datum(static::getDeterministicSecretKeyColumnName());
			$reauth_nonce = new NonceDatum(static::getReauthenticationNonceColumnName());
			$reauth_hash = new TextDatum(static::getReauthenticationHashColumnName());
			$keygenNonce = new NonceDatum("keyGenerationNonce");
			$keygenNonce->volatilize();
			$signature = new SodiumCryptoSignatureDatum(static::getSignatureColumnName());
			array_push($columns, $name, $key, $dsk, $reauth_nonce, $reauth_hash, $keygenNonce, $signature);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		foreach ($columns as $c) {
			$c->setNullable(true);
		}
	}

	public function setDeterministicSecretKey(string $value):string{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} setting DSK to value with hash " . sha1($value));
		}
		$ret = $this->setColumnValue(static::getDeterministicSecretKeyColumnName(), $value);
		if (! $this->hasDeterministicSecretKey()) {
			Debug::error("{$f} immediately after setting it, deterministic secret key is undefined");
		} elseif ($print) {
			Debug::print("{$f} deterministic secret key was set correctly");
		}
		return $ret;
	}

	public function ejectDeterministicSecretKey():?string{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasDeterministicSecretKey()) {
			Debug::print("{$f} deterministic secret key is undefined");
		} elseif ($print) {
			Debug::print("{$f} deterministic secret key is ready for ejection");
			// Debug::printSession();
		}
		$ret = $this->ejectColumnValue(static::getDeterministicSecretKeyColumnName());
		if ($this->hasDeterministicSecretKey()) {
			Debug::warning("{$f} immediately after ejection, determministic secret key is still defined");
			Debug::printSessionHash();
			Debug::printStackTrace();
		} elseif ($print) {
			Debug::print("{$f} deterministic secret key was ejected properly");
			// Debug::printStackTraceNoExit();
		}
		return $ret;
	}

	public function setUsername(string $value):string{
		return $this->setColumnValue(static::getUsernameColumnName(), $value);
	}

	public function getUsername():string{
		return $this->getColumnValue(static::getUsernameColumnName());
	}

	public function ejectUsername():string{
		return $this->ejectColumnValue(static::getUsernameColumnName());
	}

	public function ejectUserKey():string{
		return $this->ejectColumnValue(static::getUserKeyColumnName());
	}

	public function ejectUserAccountType():string{
		return $this->ejectColumnValue(static::getUserAccountTypeColumnName());
	}

	public function setUserKey(string $value):string{
		$f = __METHOD__;
		$print = false;
		$columnName = static::getUserKeyColumnName();
		if ($print) {
			Debug::print("{$f} setting column \"{$columnName}\" to \"{$value}\"");
		}
		return $this->setColumnValue($columnName, $value);
	}

	public function hasSignature():bool{
		return $this->hasColumnValue(static::getSignatureColumnName());
	}

	public function getSignature():string{
		$f = __METHOD__;
		if (! $this->hasSignature()) {
			Debug::error("{$f} signature is undefined");
		}
		return $this->getColumnValue(static::getSignatureColumnName());
	}

	public function setSignature(string $value):string{
		return $this->setColumnValue(static::getSignatureColumnName(), $value);
	}

	public function hasUserKey():bool{
		return $this->hasColumnValue(static::getUserKeyColumnName());
	}

	public function getUserKey():string{
		return $this->getColumnValue(static::getUserKeyColumnName());
	}

	public function generateReauthenticationHash(string $reauth_nonce, string $reauth_key):string{
		$f = __METHOD__;
		try {
			$this->setReauthenticationNonce($reauth_nonce);
			$hash = password_hash($reauth_nonce . $reauth_key, PASSWORD_BCRYPT);
			return $this->setReauthenticationHash($hash);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getPasswordHash():string{
		return $this->getUserData()->getPasswordHash();
	}

	public function hasDeterministicSecretKey():bool{
		return $this->hasColumnValue(static::getDeterministicSecretKeyColumnName());
	}

	public function hasKeyGenerationNonce():bool{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				$has = $this->hasColumnValue("keyGenerationNonce");
				if ($has) {
					Debug::print("{$f} yes, the key generation nonce is defined");
				} else {
					Debug::print("{$f} no, the key generation nonce is undefined");
				}
			}
			return $this->hasColumnValue("keyGenerationNonce");
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getKeyGenerationNonce():string{
		$f = __METHOD__;
		if (! $this->hasKeyGenerationNonce()) {
			Debug::error("{$f} key generation nonce is undefined");
		}
		return $this->getColumnValue("keyGenerationNonce");
	}

	/**
	 * generate the deterministic secret key, or return the existing one
	 *
	 * @return NULL|string
	 */
	public function getDeterministicSecretKey(?PlayableUser $user = null):string{
		$f = __METHOD__;
		try {
			$print = false;
			if (! $this->hasDeterministicSecretKey()) {
				if ($user == null) {
					Debug::error("{$f} you must provide a user to generate deterministic secret key");
				}
				$key = $this->generateDeterministicSecretKey($user);
				if ($print) {
					$hash = sha1($key);
					Debug::print("{$f} generated deterministic secret key with hash \"{$hash}\"");
				}
				return $this->setDeterministicSecretKey($key);
			}
			$key = $this->getColumnValue(static::getDeterministicSecretKeyColumnName());
			$hash = sha1($key);
			if ($print) {
				Debug::print("{$f} deterministic secret key has hash \"{$hash}\"");
			}
			return $key;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * sets the user object only (i.e.
	 * does not overwrite existing data)
	 *
	 * @param PlayableUser $user
	 * @return PlayableUser
	 */
	public function setUserData(PlayableUser $user):PlayableUser{
		return $this->setForeignDataStructure(static::getUserKeyColumnName(), $user);
	}

	public function setKeyGenerationNonce(string $nonce):string{
		return $this->setColumnValue("keyGenerationNonce", $nonce);
	}

	public function generateDeterministicSecretKey(PlayableUser $user):string{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} about to generate deterministic secret key");
		}
		if (! $this->hasKeyGenerationNonce()) {
			Debug::error("{$f} key generation nonce is undefined");
		}
		$nonce = $this->getKeyGenerationNonce();
		$len = strlen($nonce);
		if ($len < 16) {
			Debug::error("{$f} nonce length is {$len}");
		}
		if ($print) {
			$hash = sha1($nonce);
			Debug::print("{$f} key generation nonce has hash \"{$hash}\"");
		}
		$key = argon_hash($user->getPostedPassword(), $nonce);
		$hash = sha1($key);
		if ($print) {
			Debug::print("{$f} generated a key with hash \"{$hash}\"");
		}
		return $key;
	}

	/**
	 * asks the user object for a bunch of information
	 *
	 * @param PlayableUser $user
	 * @return PlayableUser
	 */
	public function handSessionToUser(PlayableUser $user, ?int $mode = null):PlayableUser{
		$f = __METHOD__;
		try {
			$print = false;
			if (! $user instanceof PlayableUser) {
				$gottype = is_object($user) ? $user->getClass() : gettype($user);
				Debug::error("{$f} user data is a {$gottype}");
			} elseif ($user instanceof AuthenticatedUser && ! $user->hasUsernameData()) {
				if (! $user->hasUsernameKey()) {
					Debug::error("{$f} username key is not defined");
				}
				$decl = $user->getDeclarationLine();
				$key = $user->getIdentifierValue();
				Debug::error("{$f} user with key \"{$key}\" does not have username data; instantited {$decl}");
			}
			$key = $user->getIdentifierValue();
			$name = $user->getName();
			if ($print) {
				$class = get_class($user);
				Debug::print("{$f} handing session control to user \"{$name}\" of class \"{$class}\" with key \"{$key}\"");
			}
			$lang = $user->getLanguagePreference();
			if(!$user->hasRegionCode()){
				$decl = $user->getDeclarationLine();
				Debug::error("{$f} user's country code is undefined. Instantiated {$decl}");
			}
			$region = $user->getRegionCode();
			$lsd = new LanguageSettingsData();
			if($lsd->getLanguageCode() !== $lang){
				if($print){
					Debug::print("{$f} user has a different language code from the one defined in language settings data");
				}
				$user_class = get_class($user);
				$dummy = new $user_class(ALLOCATION_MODE_SUBJECTIVE);
				foreach($dummy->getColumns() as $name => $column){
					if($column->hasHumanReadableName()){
						$user->getColumn($name)->setHumanReadableName($column->getHumanReadableName());
					}
				}
			}elseif($print){
				Debug::print("{$f} user has the same language code as defined in language settings data");
			}
			$lsd->setLanguageCode($lang);
			$lsd->setRegionCode($region);
			$locale = $user->getLocaleString();
			if(!is_dir("/var/www/locale/{$locale}")){
				$locale = Internationalization::getFallbackLocale($locale);
			}
			$set = setlocale(LC_MESSAGES, $locale, "{$locale}.utf8", "{$locale}.UTF8");
			if(false === $set){
				Debug::error("{$f} setting locale failed");
			}elseif($print){
				Debug::print("{$f} successfully set locale to \"".getlocale(LC_MESSAGES)."\"");
			}
			$this->setUserData($user);
			if ($print) {
				Debug::print("{$f} setting username to \"{$name}\"");
			}
			$this->setUsername($name);
			$this->setUserKey($key);
			if ($user->hasKeyGenerationNonce()) {
				$this->setKeyGenerationNonce($user->getKeyGenerationNonce());
				if (! $this->hasKeyGenerationNonce()) {
					$this->unsetColumnValues();
					Debug::error("{$f} immediately after setting it, key generation nonce is undefined");
				}
			} elseif ($print) {
				Debug::error("{$f} user does not have a defined key generation nonce");
			}
			$this->setUserAccountType($user->getAccountType());
			if (! $this->hasDeterministicSecretKey()) {
				if ($print) {
					Debug::print("{$f} deterministic secret key is undefined");
				}
				$this->setDeterministicSecretKey($this->generateDeterministicSecretKey($user)); // doesn't work for reauthentication
			}
			if (! $this->hasDeterministicSecretKey()) {
				Debug::error("{$f} dsk is undefined immediately after generation");
			} elseif ($print) {
				Debug::print("{$f} deterministic secret key is defined");
			}
			if (cache()->enabled() && USER_CACHE_ENABLED) {
				$user_key = $user->getIdentifierValue();
				if ($user->hasCacheValue()) {
					if ($print) {
						Debug::print("{$f} caching user data now");
					}
					$cache_value = $user->getCacheValue();
					cache()->setAPCu($user_key, $cache_value, $user->getTimeToLive());
				} elseif ($user instanceof AnonymousUser) {
					// ok
					if ($print) {
						Debug::print("{$f} unregistered user does not have a temporary cache value");
					}
					cache()->expireAPCu($user_key, $user->getTimeToLive());
				} else {
					Debug::error("{$f} user data does not have a chacheable value");
				}
			} elseif ($print) {
				Debug::print("{$f} user cache is disabled");
			}
			return $this->setUserData($user);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPrettyClassName():string{
		return _("Authentication data");
	}

	public static function getPrettyClassNames():string{
		return static::getPrettyClassName();
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}
}
