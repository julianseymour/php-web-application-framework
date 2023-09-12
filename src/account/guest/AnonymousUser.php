<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\guest;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\default_lang_ip;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\SelfPermission;
use JulianSeymour\PHPWebApplicationFramework\account\avatar\ProfileImageData;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\Workflow;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use JulianSeymour\PHPWebApplicationFramework\security\throttle\GenericThrottleMeter;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryData;
use Exception;
use mysqli;

class AnonymousUser extends PlayableUser{

	public function updateLogoutTimestamp(): int{
		return SUCCESS;
	}

	public static function getPassword(): string{
		return base64_encode(random_bytes(32));
	}

	public static function getAccountTypeStatic():string{
		return ACCOUNT_TYPE_GUEST;
	}

	public function getEmailNotificationStatus(string $type):bool{
		return false;
	}

	public function getPushNotificationStatus(string $type):bool{
		return true;
	}

	/**
	 * generates the bare minimum values needed for a guest user to function without any persistence
	 *
	 * @return int
	 */
	public function stubbify(): int{
		$f = __METHOD__;
		$this->setReceptivity(DATA_MODE_PASSIVE);
		$this->setSerialNumber(0);
		$this->generateKey();
		$this->processPasswordData(PasswordData::generate(base64_encode(random_bytes(32))));
		if(!$this->hasKeyGenerationNonce()) {
			Debug::error("{$f} key generation nonce is undefined");
		}
		$this->disable();
		return SUCCESS;
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_INSERT:
				return new AnonymousAccountTypePermission($name);
			case DIRECTIVE_UPDATE:
				return new SelfPermission($name);
			default:
		}
		return parent::getPermissionStatic($name, $data);
	}

	public function hasName(): bool{
		return true;
	}

	public function getAccountType(): string{
		return static::getAccountTypeStatic();
	}

	public final function getName(): string{
		$f = __METHOD__;
		try{
			if(isset($this->name)) {
				return $this->name;
			}
			$anonymous = _("Anonymous");
			if(!$this->hasSerialNumber()) {
				return $anonymous;
			}
			$num = $this->getSerialNumber();
			$name = "{$anonymous} #{$num}";
			return $name;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getDisplayName(): string{
		return $this->getName();
	}

	/**
	 *
	 * @param int $mode
	 * @return FullAuthenticationData
	 */
	protected static function createAuthenticationData($mode = LOGIN_TYPE_UNDEFINED): FullAuthenticationData{
		return new FullAuthenticationData();
	}

	public function filterIpAddress(mysqli $mysqli, ?string $ip_address = null, bool $skip_insert = false): int{
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::warning("{$f} not implemented: global blacklist");
		}
		return SUCCESS;
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			// Debug::print("{$f} entered");
			parent::reconfigureColumns($columns, $ds);
			$indices = [
				'subtype'
			];
			foreach($indices as $index) {
				$columns[$index]->volatilize();
			}
			$columns[static::getIdentifierNameStatic()]->setNullable(true);
			$columns[static::getIdentifierNameStatic()]->setDefaultValue(null);
			// Debug::print("{$f} returning normally");
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function preventDuplicateEntry($mysqli): int{
		return SUCCESS;
	}
	
	public function getHardResetCount(): int{
		return 0;
	}

	public function getVirtualColumnValue(string $column_name){
		switch ($column_name) {
			case "emailAddress":
				return "undefined";
			case "name":
				return $this->getName();
			default:
				return parent::getVirtualColumnValue($column_name);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch ($column_name) {
			case "emailAddress":
			case "name":
				return true;
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public static function getTableNameStatic(): string{
		return "guests";
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$ever = new BooleanDatum("hasEverAuthenticated");
		$ever->setDefaultValue(false);
		$name = new VirtualDatum("name");
		$emailAddress = new VirtualDatum("emailAddress");
		array_push($columns, $ever, $name, $emailAddress);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"preinsert"
		]);
	}

	public function getHasEverAuthenticated(): bool{
		return $this->getColumnValue("hasEverAuthenticated");
	}

	public function setHasEverAuthenticated($value): bool{
		return $this->setColumnValue("hasEverAuthenticated", $value);
	}

	public function __construct(?int $mode = ALLOCATION_MODE_EAGER){
		$f = __METHOD__;
		try{
			// Debug::print("{$f} entered; about to call parent function");
			parent::__construct($mode);
			$this->setAccountType($this->getAccountTypeStatic());
			$session = new LanguageSettingsData();
			if($session->hasLanguageCode()) {
				// Debug::print("{$f} language code is already defined");
				$this->setLanguagePreference($session->getLanguageCode());
				return;
			}
			$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : SERVER_PUBLIC_IP_ADDRESS;
			$language = default_lang_ip($ip);
			$session->setLanguageCode($this->setLanguagePreference($language));
			// Debug::print("{$f} returned from parent function");
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setIdentifierValue($key): string{
		$f = __METHOD__;
		try{
			// Debug::print("{$f} entered");
			if(! isset($key)) {
				Debug::error("{$f} key \"{$key}\" is invalid");
			}elseif($this->hasIdentifierValue()) {
				Debug::error("{$f} key was already set");
			}
			// Debug::print("{$f} returning parent function");
			return parent::setIdentifierValue($key);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getPostedPassword(): string{
		return random_bytes(32);
	}

	public function getLastAuthenticatedIpAddress(): string{
		$f = __METHOD__;
		Debug::printStackTraceNoExit("{$f} ???");
		return $_SERVER['REMOTE_ADDR'];
	}

	public function initializeAnonymousSession(): int{
		$f = __METHOD__;
		try{
			$print = false;
			$session = new FullAuthenticationData();
			if($session->hasDeterministicSecretKey()) {
				$session->ejectDeterministicSecretKey();
			}
			$lsd = new LanguageSettingsData();
			$this->setRegionCode($lsd->getRegionCode());
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if(! isset($mysqli)) {
				Debug::warning("{$f} mysqli object returned null");
				$this->generateKey();
				$this->setEnabled(false);
				$session->handSessionToUser($this, LOGIN_TYPE_UNDEFINED);
				return SUCCESS;
			}else{
				$this->generateKey();
				$this->setNotificationDeliveryTimestamp($this->generateInsertTimestamp());
				if($print) {
					$key = $this->getIdentifierValue();
					Debug::print("{$f} generated key \"{$key}\"");
				}
				// copied
				$password_data = PasswordData::generate($this->getPassword());
				$this->setReceptivity(DATA_MODE_RECEPTIVE);
				$this->processPasswordData($password_data);
				$this->setReceptivity(DATA_MODE_DEFAULT);
				if(!$this->hasKeyGenerationNonce()) {
					Debug::error("{$f} key generation nonce is undefined");
				}
				if(cache()->enabled() && USER_CACHE_ENABLED) {
					$columns = $this->getFilteredColumns(COLUMN_FILTER_DATABASE, COLUMN_FILTER_VALUED);
					$cached_values = [];
					foreach($columns as $column_name => $column) {
						$cached_values[$column_name] = $column->getDatabaseEncodedValue();
					}
					$this->setCacheValue($cached_values);
				}elseif($print) {
					Debug::print("{$f} user cache is disabled");
				}
				$session->handSessionToUser($this, LOGIN_TYPE_UNDEFINED);
				$user = $this;
				if(app()->hasWorkflow()){
					app()->getWorkflow()->addEventListener(EVENT_AFTER_RESPOND, function (AfterRespondEvent $event, Workflow $target) use ($f, $print, $user) {
						$mysqli = db()->reconnect(PublicWriteCredentials::class);
						$status = $user->throttledInsert($mysqli);
						if($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} throttledInsert returned error status \"{$err}\"");
							$user->setObjectStatus($status);
						}elseif($print) {
							Debug::print("{$f} throttledInsert successful");
						}
					});
				}else{
					Debug::warning("{$f} application runtime does not know about workflow. This should almost never happen.");
				}
				$recovery = new SessionRecoveryData();
				$recovery->setUserData($this);
				$recovery->generateKey();
				$recovery->generateCookie();
				$this->setSessionRecoveryData($recovery);
			}
			$has_session = $session->hasUserKey();
			if(!$has_session) {
				Debug::error("{$f} user key is undefined");
			}elseif($print) {
				Debug::print("{$f} user key is " . $session->getUserKey());
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getSessionRecoveryData(): SessionRecoveryData{
		$f = __METHOD__;
		if(!$this->hasSessionRecoveryData()) {
			Debug::error("{$f} session recovery data is undefined");
		}
		return $this->getForeignDataStructure("sessionRecoveryKey");
	}

	public function hasSessionRecoveryData(): bool{
		return $this->hasForeignDataStructure("sessionRecoveryKey");
	}

	public function setSessionRecoveryData(SessionRecoveryData $struct): SessionRecoveryData{
		return $this->setForeignDataStructure("sessionRecoveryKey", $struct);
	}

	public function throttledInsert(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			$meter = new GenericThrottleMeter();
			$meter->setLimitPerMinute(1);
			$meter->setLimitPerHour(10);
			$meter->setLimitPerDay(20);
			$meter->setLimitPerWeek(30);
			$meter->setLimitPerMonth(40);
			$meter->setLimitPerYear(50);
			$query = $this->select();
			if($meter->meter($mysqli, time(), OPERATOR_LESSTHANEQUALS, $query->where("insertIpAddress")
				->withTypeSpecifier('s')
				->withParameters([
				$_SERVER['REMOTE_ADDR']
			]))) {
				if($print) {
					Debug::print("{$f} user is probably not trying to flood the server with billions of fake sessions");
				}
				app()->setUserData($this);
				$status = $this->insert($mysqli);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} database write operation returned failure status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
				// insert session recovery data
				$recovery = $this->getSessionRecoveryData(); // new SessionRecoveryData();
				                                             // $recovery->setUserData($this);
				$status = $recovery->insert($mysqli);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} inserting session recovery data returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} successfully inserted session recovery data");
				}
			}else{
				Debug::warning("{$f} too many users with this IP address, skipping creation of a new session");
				$this->setEnabled(false);
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function isPublic(): bool{
		return true;
	}

	public function loadFailureHook(): int{
		$f = __METHOD__;
		try{
			// Debug::print("{$f} user does not exist -- deleting cookie now");
			unset($_COOKIE['anonSessionToken']);
			unset($_SESSION['anonSessionToken']);
			// Debug::print("{$f} you need to initialize another session once you get back to getAnonymousUser");
			return $this->setObjectStatus(ERROR_NOT_FOUND);
			return parent::loadFailureHook();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getPrettyClassName():string{
		return _("Anonymous user");
	}

	public function getProfileImageData(): ?ProfileImageData{
		return null;
	}

	public static function getPrettyClassNames():string{
		return _("Anonymous users");
	}
}
