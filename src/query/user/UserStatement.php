<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use function JulianSeymour\PHPWebApplicationFramework\is_json;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\LockOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\release;

abstract class UserStatement extends QueryStatement{

	use CommentTrait;
	use LockOptionTrait;
	use MultipleDatabaseUserDefinitionsTrait;

	protected $_attribute;

	protected $failedLoginAttemptsCount;

	protected $maxConnectionsPerHourCount;

	protected $maxQueriesPerHourCount;

	protected $maxUpdatesPerHourCount;

	protected $maxUserConnectionsCount;

	protected $passwordExpires;

	protected $passwordHistoryOption;

	protected $passwordLockTimeDays;

	protected $requireCurrentPasswordOption;

	protected $passwordReuseIntervalOption;

	protected $tlsCipher;

	protected $tlsIssuer;

	protected $tlsSubject;

	public function __construct(...$users){
		parent::__construct();
		$this->requirePropertyType("users", DatabaseUserDefinition::class);
		if(isset($users)){
			$this->setUsers($users);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"passwordExpired",
			"requireCurrentPassword",
			"requireNone",
			"SSL",
			"X509"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"passwordExpired",
			"requireCurrentPassword",
			"requireNone",
			"SSL",
			"X509"
		]);
	}
	
	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->propertyTypes, $deallocate);
		$this->release($this->_attribute, $deallocate);
		$this->release($this->comment, $deallocate);
		$this->release($this->failedLoginAttemptsCount, $deallocate);
		$this->release($this->lockOption, $deallocate);
		$this->release($this->maxConnectionsPerHourCount, $deallocate);
		$this->release($this->maxQueriesPerHourCount, $deallocate);
		$this->release($this->maxUpdatesPerHourCount, $deallocate);
		$this->release($this->maxUserConnectionsCount, $deallocate);
		$this->release($this->passwordExpires, $deallocate);
		$this->release($this->passwordHistoryOption, $deallocate);
		$this->release($this->passwordLockTimeDays, $deallocate);
		$this->release($this->requireCurrentPasswordOption, $deallocate);
		$this->release($this->passwordReuseIntervalOption, $deallocate);
		$this->release($this->tlsCipher, $deallocate);
		$this->release($this->tlsIssuer, $deallocate);
		$this->release($this->tlsSubject, $deallocate);
	}
	
	public function setRequireCurrentPasswordFlag(bool $value = true):bool{
		return $this->setFlag("requireCurrentPassword");
	}

	public function getRequireCurrentPasswordFlag():bool{
		return $this->getFlag("requireCurrentPassword");
	}

	public function setPasswordExpiredFlag(bool $value = true):bool{
		return $this->setFlag("passwordExpired", $value);
	}

	public function getPasswordExpiredFlag():bool{
		return $this->getFlag("passwordExpired");
	}

	public function setRequireNoneFlag(bool $value = true):bool{
		return $this->setFlag("requireNone", $value);
	}

	public function getRequireNoneFlag():bool{
		return $this->getFlag("requireNone");
	}

	public function requireNone(bool $value=true):UserStatement{
		$this->setRequireNoneFlag($value);
		return $this;
	}

	public function defaultRole(...$values):UserStatement{
		$this->setRoles($values);
		return $this;
	}

	public function setSSLFlag(bool $value = true):bool{
		return $this->setFlag("SSL", $value);
	}

	public function getSSLFlag():bool{
		return $this->getFlag("SSL");
	}

	public function setX509Flag(bool $value = true):bool{
		return $this->setFlag("X509", $value);
	}

	public function getX509Flag():bool{
		return $this->getFlag("X509");
	}

	public function setCipher($cipher){
		$f = __METHOD__;
		if(!is_string($cipher)){
			Debug::error("{$f} cipher must be a string");
		}elseif($this->hasCipher()){
			$this->release($this->tlsCipher);
		}
		return $this->tlsCipher = $this->claim($cipher);
	}

	public function hasCipher():bool{
		return isset($this->cipher);
	}

	public function getCipher(){
		$f = __METHOD__;
		if(!$this->hasCipher()){
			Debug::error("{$f} cipher is undefined");
		}
		return $this->tlsCipher;
	}

	public function cipher($cipher):UserStatement{
		$this->setCipher($cipher);
		return $this;
	}

	public function setIssuer($issuer){
		$f = __METHOD__;
		if(!is_string($issuer)){
			Debug::error("{$f} issuer must be a string");
		}elseif($this->hasIssuer()){
			$this->release($this->tlsIssuer);
		}
		return $this->tlsIssuer = $this->claim($issuer);
	}

	public function hasIssuer():bool{
		return isset($this->tlsIssuer);
	}

	public function getIssuer(){
		$f = __METHOD__;
		if(!$this->hasIssuer()){
			Debug::error("{$f} issuer is undefined");
		}
		return $this->tlsIssuer;
	}

	public function issuer($issuer):UserStatement{
		$this->setIssuer($issuer);
		return $this;
	}

	public function setSubject($subject){
		$f = __METHOD__;
		if(!is_string($subject)){
			Debug::error("{$f} subject is undefined");
		}elseif($this->hasSubject()){
			$this->release($this->tlsSubject);
		}
		return $this->tlsSubject = $this->claim($subject);
	}

	public function hasSubject():bool{
		return isset($this->tlsSubject);
	}

	public function getSubject(){
		$f = __METHOD__;
		if(!$this->hasSubject()){
			Debug::error("{$f} subject is undefined");
		}
		return $this->tlsSubject;
	}

	public function subject($subject):UserStatement{
		$this->setSubject($subject);
		return $this;
	}

	public function hasTLSOptions():bool{
		return $this->getSSLFlag() || $this->getX509Flag() || $this->hasCipher() || $this->hasIssuer() || $this->hasSubject();
	}

	public function setMaxQueriesPerHour($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1){
			Debug::error("{$f} input parameter must be positive");
		}elseif($this->hasMaxQueriesPerHour()){
			$this->release($this->maxQueriesPerHourCount);
		}
		return $this->maxQueriesPerHourCount = $this->claim($count);
	}

	public function hasMaxQueriesPerHour():bool{
		return isset($this->maxQueriesPerHourCount);
	}

	public function getMaxQueriesPerHour(){
		$f = __METHOD__;
		if(!$this->hasMaxQueriesPerHour()){
			Debug::error("{$f} undefined");
		}
		return $this->maxQueriesPerHourCount;
	}

	public function maxQueriesPerHour($count):UserStatement{
		$this->setMaxQueriesPerHour($count);
		return $this;
	}

	public function setMaxUpdatesPerHour($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1){
			Debug::error("{$f} input parameter must be positive");
		}elseif($this->hasMaxUpdatesPerHour()){
			$this->release($this->maxUserConnectionsCount);
		}
		return $this->maxUpdatesPerHourCount = $this->claim($count);
	}

	public function hasMaxUpdatesPerHour():bool{
		return isset($this->maxUpdatesPerHourCount);
	}

	public function getMaxUpdatesPerHour(){
		$f = __METHOD__;
		if(!$this->hasMaxUpdatesPerHour()){
			Debug::error("{$f} undefined");
		}
		return $this->maxUpdatesPerHourCount;
	}

	public function maxUpdatesPerHour($count):UserStatement{
		$this->setMaxUpdatesPerHour($count);
		return $this;
	}

	public function setMaxConnectionsPerHour($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1){
			Debug::error("{$f} input parameter must be positive");
		}elseif($this->hasMaxConnectionsPerHour()){
			$this->release($this->maxConnectionsPerHourCount);
		}
		return $this->maxConnectionsPerHourCount = $this->claim($count);
	}

	public function hasMaxConnectionsPerHour():bool{
		return isset($this->maxConnectionsPerHourCount);
	}

	public function getMaxConnectionsPerHour(){
		$f = __METHOD__;
		if(!$this->hasMaxConnectionsPerHour()){
			Debug::error("{$f} undefined");
		}
		return $this->maxConnectionsPerHourCount;
	}

	public function maxConnectionsPerHour($count):UserStatement{
		$this->setMaxConnectionsPerHour($count);
		return $this;
	}

	public function setMaxUserConnections($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1){
			Debug::error("{$f} input parameter must be positive");
		}elseif($this->hasMaxUserConnections()){
			$this->release($this->maxUserConnectionsCount);
		}
		return $this->maxUserConnectionsCount = $this->claim($count);
	}

	public function hasMaxUserConnections():bool{
		return isset($this->maxUserConnectionsCount);
	}

	public function getMaxUserConnections(){
		$f = __METHOD__;
		if(!$this->hasMaxUserConnections()){
			Debug::error("{$f} undefined");
		}
		return $this->maxUserConnectionsCount;
	}

	public function maxUserConnections($count):UserStatement{
		$this->setMaxUserConnections($count);
		return $this;
	}

	public function hasResourceOptions():bool{
		return $this->hasMaxQueriesPerHour() || $this->hasMaxUpdatesPerHour() || $this->hasMaxConnectionsPerHour() || $this->hasMaxUserConnections();
	}

	public function setComment($comment){
		$f = __METHOD__;
		try{
			if($this->hasAttribute()){
				Debug::error("{$f} you cannot have both comment and attribute in the same query");
			}elseif(!is_string($comment)){
				$comment = "{$comment}";
			}
			// $this->setRequiredMySQLVersion("8.0.21");
			if($this->hasComment()){
				$this->release($this->comment);
			}
			return $this->comment = $this->claim($comment);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setAttribute($attr){
		$f = __METHOD__;
		if($this->hasComment()){
			Debug::error("{$f} you cannot have both comment and attribute in the same query");
		}elseif(is_array($attr)){
			$attr = json_encode($attr);
		}
		if(!is_json($attr)){
			Debug::error("{$f} attribute is not valid JSON");
		}
		// $this->setRequiredMySQLVersion("8.0.21");
		if($this->hasAttribute()){
			$this->release($this->_attribute);
		}
		return $this->_attribute = $this->claim($attr);
	}

	public function hasAttribute():bool{
		return isset($this->_attribute) && is_json($this->_attribute);
	}

	public function getAttribute(){
		$f = __METHOD__;
		if(!$this->hasAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->_attribute;
	}

	public function setLockOption($opt){
		$f = __METHOD__;
		if(!is_string($opt)){
			Debug::error("{$f} lock option must be a string");
		}
		$opt = strtolower($opt);
		switch($opt){
			case LOCK_OPTION_LOCK:
			case LOCK_OPTION_UNLOCK:
				break;
			default:
				Debug::error("{$f} invalid lock option \"{$opt}\"");
		}
		if($this->hasLockOption()){
			$this->release($this->lockOption);
		}
		return $this->lockOption = $this->claim($opt);
	}

	public function accountLock():UserStatement{
		$this->setLockOption(LOCK_OPTION_LOCK);
		return $this;
	}

	public function accountUnlock():UserStatement{
		$this->setLockOption(LOCK_OPTION_UNLOCK);
		return $this;
	}

	public function setPasswordLockTime($n){
		$f = __METHOD__;
		if(is_string($n)){
			$n = strtolower($n);
			if($n !== PASSWORD_OPTION_UNBOUNDED){
				Debug::error("{$f} invalid password lock option \"{$n}\"");
			}
		}elseif(is_int($n)){
			if($n < 1){
				Debug::error("{$f} password lock time must be a positive integer");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		if($this->hasPasswordLockTime()){
			$this->release($this->passwordLockTimeDays);
		}
		return $this->passwordLockTimeDays = $this->claim($n);
	}

	public function hasPasswordLockTime():bool{
		return isset($this->passwordLockTimeDays);
	}

	public function getPasswordLockTime(){
		$f = __METHOD__;
		if(!$this->hasPasswordLockTime()){
			Debug::error("{$f} password lock time is undefined");
		}
		return $this->passwordLockTimeDays;
	}

	public function passwordLockTime($n):UserStatement{
		$this->setPasswordLockTime($n);
		return $this;
	}

	public function setFailedLoginAttempts($n){
		$f = __METHOD__;
		if(!is_int($n)){
			Debug::error("{$f} failed login attempt count must be a non-negative integer");
		}elseif($n < 0){
			Debug::error("{$f} failed login attempt count must be non-negative");
		}elseif($this->hasFailedLoginAttempts()){
			$this->release($this->failedLoginAttemptsCount);
		}
		return $this->failedLoginAttemptsCount = $this->claim($n);
	}

	public function hasFailedLoginAttempts():bool{
		return isset($this->failedLoginAttemptsCount) && is_int($this->failedLoginAttemptsCount);
	}

	public function getFailedLoginAttempts(){
		$f = __METHOD__;
		if(!$this->hasFailedLoginAttempts()){
			Debug::error("{$f} failed login attempt count is undefined");
		}
		return $this->failedLoginAttemptsCount;
	}

	public function failedLoginAttempts($n):UserStatement{
		$this->setFailedLoginAttempts($n);
		return $this;
	}

	public function setRequireCurrentPasswordOption($opt){
		$f = __METHOD__;
		if(!is_string($opt)){
			Debug::error("{$f} require current password option must be a string");
		}
		$opt = strtolower($opt);
		switch($opt){
			case CONST_DEFAULT:
			case PASSWORD_OPTION_OPTIONAL:
				break;
			default:
				Debug::error("{$f} invalid option \"{$opt}\"");
		}
		if($this->hasRequireCurrentPasswordOption()){
			$this->release($this->requireCurrentPasswordOption);
		}
		return $this->requireCurrentPasswordOption = $this->claim($opt);
	}

	public function hasRequireCurrentPasswordOption():bool{
		return isset($this->requireCurrentPasswordOption);
	}

	public function getRequireCurrentPasswordOption(){
		if(!$this->hasRequireCurrentPasswordOption()){
			return CONST_DEFAULT;
		}
		return $this->requireCurrentPasswordOption;
	}

	public function passwordRequireCurrent($opt = null):UserStatement{
		if($opt === null){
			$this->setRequireCurrentPasswordFlag(true);
		}else{
			$this->setRequireCurrentPasswordOption($opt);
		}
		return $this;
	}

	public function setPasswordReuseInterval($count){
		$f = __METHOD__;
		if(is_string($count)){
			$count = strtolower($count);
			if($count !== CONST_DEFAULT){
				Debug::error("{$f} invalid password reuse interval option \"{$count}\"");
			}
		}elseif(is_int($count)){
			if($count < 1){
				Debug::error("{$f} password reuse interval must be a positive integer");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		if($this->hasPasswordReuseInterval()){
			$this->release($this->passwordReuseIntervalOption);
		}
		return $this->passwordReuseIntervalOption = $this->claim($count);
	}

	public function hasPasswordReuseInterval():bool{
		return isset($this->passwordReuseIntervalOption);
	}

	public function getPasswordReuseInterval(){
		if(!$this->hasPasswordReuseInterval()){
			return CONST_DEFAULT;
		}
		return $this->passwordReuseIntervalOption;
	}

	public function passwordReuseInterval($count):UserStatement{
		$this->setPasswordReuseInterval($count);
		return $this;
	}

	public function setPasswordHistoryOption($count){
		$f = __METHOD__;
		if(is_string($count)){
			$count = strtolower($count);
			if($count !== CONST_DEFAULT){
				Debug::error("{$f} invalid password history option \"{$count}\"");
			}
		}elseif(is_int($count)){
			if($count < 0){
				Debug::error("{$f} password history count cannot be negative");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		if($this->hasPasswordHistoryOption()){
			$this->release($this->passwordHistoryOption);
		}
		return $this->passwordHistoryOption = $this->claim($count);
	}

	public function hasPasswordHistoryOption():bool{
		return isset($this->passwordHistoryOption);
	}

	public function getPasswordHistoryOption(){
		$f = __METHOD__;
		if(!$this->hasPasswordHistory()){
			Debug::error("{$f} password history is undefined");
		}
		return $this->passwordHistoryOption;
	}

	public function passwordHistory($count):UserStatement{
		$this->setPasswordHistoryOption($count);
		return $this;
	}

	public function setPasswordExpiration($interval){
		$f = __METHOD__;
		if(is_string($interval)){
			$interval = strtolower($interval);
			switch($interval){
				case CONST_DEFAULT:
				case PASSWORD_OPTION_NEVER:
					break;
				default:
					Debug::error("{$f} invalid password expiration string \"{$interval}\"");
			}
		}elseif(is_int($interval)){
			if($interval < 1){
				Debug::error("{$f} interval must be a positive integer");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		if($this->hasPasswordExpiration()){
			$this->release($this->passwordExpires);
		}
		return $this->passwordExpires = $this->claim($interval);
	}

	public function hasPasswordExpiration():bool{
		return isset($this->passwordExpires);
	}

	public function getPasswordExpiration(){
		if(!$this->hasPasswordExpiration()){
			return CONST_DEFAULT;
		}
		return $this->passwordExpires;
	}

	public function passwordExpire($interval = null):UserStatement{
		if($interval === null){
			$this->setPasswordExpiredFlag(true);
		}else{
			$this->setPasswordExpiration($interval);
		}
		return $this;
	}

	protected function getTLSOptionsString():string{
		$f = __METHOD__;
		$string = "require";
		if($this->getRequireNoneFlag()){
			$string .= " none";
		}elseif($this->hasTLSOptions()){
			if($this->getSSLFlag()){
				$string .= " SSL";
			}
			if($this->getX509Flag()){
				$string .= " X509";
			}
			if($this->hasCipher()){
				$string .= " cipher '" . escape_quotes($this->getCipher(), QUOTE_STYLE_SINGLE) . "'";
			}
			if($this->hasIssuer()){
				$string .= " issuer '" . escape_quotes($this->getIssuer(), QUOTE_STYLE_SINGLE) . "'";
			}
			if($this->hasSubject()){
				$string .= " subject '" . escape_quotes($this->getSubject(), QUOTE_STYLE_SINGLE) . "'";
			}
		}else{
			Debug::error("{$f} neither of the above");
		}
		return $string;
	}

	protected function getResourceOptionsString():string{
		$f = __METHOD__;
		if(!$this->hasResourceOptions()){
			Debug::error("{$f} resource options are undefined");
		}
		$string = " with ";
		// MAX_QUERIES_PER_HOUR count
		if($this->hasMaxQueriesPerHour()){
			$string .= "max_queries_per_hour " . $this->getMaxQueriesPerHour();
		}
		// | MAX_UPDATES_PER_HOUR count
		if($this->hasMaxUpdatesPerHour()){
			$string .= "max_updates_per_hour " . $this->getMaxUpdatesPerHour();
		}
		// | MAX_CONNECTIONS_PER_HOUR count
		if($this->hasMaxConnectionsPerHour()){
			$string .= "max_connections_per_hour " . $this->getMaxConnectionsPerHour();
		}
		// | MAX_USER_CONNECTIONS count
		if($this->hasMaxUserConnections()){
			$string .= "max_user_connection " . $this->getMaxUserConnections();
		}
		return $string;
	}

	public function hasPasswordOptions():bool{
		return $this->hasPasswordExpiration() || $this->getPasswordExpiredFlag() || $this->hasPasswordHistoryOption() || $this->hasPasswordReuseInterval() || $this->getRequireCurrentPasswordFlag() || $this->hasRequireCurrentPasswordOption() || $this->hasLockOption() || ($this->hasFailedLoginAttempts() && $this->hasPasswordLockTime() && hasMinimumMySQLVersion("8.0.19"));
	}

	protected function getPasswordOptionsString():bool{
		$f = __METHOD__;
		if(!$this->hasPasswordOptions()){
			Debug::error("{$f} password options are undefined");
		}
		$string = "";
		// PASSWORD EXPIRE [DEFAULT | NEVER | INTERVAL N DAY]
		if($this->hasPasswordExpiration() || $this->getPasswordExpiredFlag()){
			$string .= " password expire";
			if(!$this->getPasswordExpiredFlag() && $this->hasPasswordExpiration()){
				$string .= " ";
				$interval = $this->getPasswordExpiration();
				if(is_int($interval)){
					$string .= "interval {$interval} day";
				}elseif(is_string($interval)){
					$string .= $interval;
				}else{
					Debug::error("{$f} neither of the above");
				}
			}
		}
		// | PASSWORD HISTORY {DEFAULT | N}
		if($this->hasPasswordHistoryOption()){
			$string .= " password history " . $this->getPasswordHistoryOption();
		}
		// | PASSWORD REUSE INTERVAL {DEFAULT | N DAY}
		if($this->hasPasswordReuseInterval()){
			$interval = $this->getPasswordReuseInterval();
			$string .= " password reuse interval {$interval}";
			if(is_int($interval)){
				$string .= " day";
			}
		}
		// | PASSWORD REQUIRE CURRENT [DEFAULT | OPTIONAL]
		if($this->getRequireCurrentPasswordFlag() || $this->hasRequireCurrentPasswordOption()){
			$string .= " password require current";
			if($this->hasRequireCurrentPasswordOption()){
				$string .= " " . $this->getRequireCurrentPasswordOption();
			}
		}
		if($this->hasFailedLoginAttempts() && $this->hasPasswordLockTime() && hasMinimumMySQLVersion("8.0.19")){
			// | FAILED_LOGIN_ATTEMPTS N
			$string .= " failed_login_attempts " . $this->getFailedLoginAttempts();
			// | PASSWORD_LOCK_TIME {N | UNBOUNDED}
			$string .= " password_lock_time " . $this->getPasswordLockTime();
		}
		// [{ ACCOUNT LOCK | ACCOUNT UNLOCK }]
		if($this->hasLockOption()){
			$string .= " account " . $this->getLockOption();
		}
		return $string;
	}

	protected function getCommentAttributeString():string{
		$f = __METHOD__;
		if(!hasMinimumMySQLVersion("8.0.21")){
			Debug::error("{$f} insufficient MySQL version");
		}
		if($this->hasComment()){
			return " comment '" . escape_quotes($this->getComment(), QUOTE_STYLE_SINGLE) . "'";
		}elseif($this->hasAttribute()){
			$attr = escape_quotes($this->getAttribute(), QUOTE_STYLE_SINGLE);
			return " attribute '{$attr}'";
		}
		Debug::error("{$f} comment and attribute are undefined");
	}
}
