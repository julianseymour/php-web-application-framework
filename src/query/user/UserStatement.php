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

abstract class UserStatement extends QueryStatement
{

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

	public function __construct(...$users)
	{
		parent::__construct();
		$this->requirePropertyType("users", DatabaseUserDefinition::class);
		if(isset($users)) {
			$this->setUsers($users);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"passwordExpired",
			"requireCurrentPassword",
			"requireNone",
			"SSL",
			"X509"
		]);
	}

	public function setRequireCurrentPasswordFlag($value = true)
	{
		return $this->setFlag("requireCurrentPassword");
	}

	public function getRequireCurrentPasswordFlag()
	{
		return $this->getFlag("requireCurrentPassword");
	}

	public function setPasswordExpiredFlag($value = true)
	{
		return $this->setFlag("passwordExpired", $value);
	}

	public function getPasswordExpiredFlag()
	{
		return $this->getFlag("passwordExpired");
	}

	public function setRequireNoneFlag($value = true)
	{
		return $this->setFlag("requireNone", $value);
	}

	public function getRequireNoneFlag()
	{
		return $this->getFlag("requireNone");
	}

	public function requireNone()
	{
		$this->setRequireNoneFlag(true);
		return $this;
	}

	public function defaultRole(...$values)
	{
		$this->setRoles($values);
		return $this;
	}

	public function setSSLFlag($value = true)
	{
		return $this->setFlag("SSL", $value);
	}

	public function getSSLFlag()
	{
		return $this->getFlag("SSL");
	}

	public function setX509Flag($value = true)
	{
		return $this->setFlag("X509", $value);
	}

	public function getX509Flag()
	{
		return $this->getFlag("X509");
	}

	public function setCipher($cipher)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setCipher()";
		if($cipher == null) {
			unset($this->tlsCipher);
			return null;
		}elseif(!is_string($cipher)) {
			Debug::error("{$f} cipher must be a string");
		}
		return $this->tlsCipher = $cipher;
	}

	public function hasCipher()
	{
		return isset($this->cipher);
	}

	public function getCipher()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getCipher()";
		if(!$this->hasCipher()) {
			Debug::error("{$f} cipher is undefined");
		}
		return $this->tlsCipher;
	}

	public function cipher($cipher)
	{
		$this->setCipher($cipher);
		return $this;
	}

	public function setIssuer($issuer)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setIssuer()";
		if($issuer == null) {
			unset($this->tlsIssuer);
			return null;
		}elseif(!is_string($issuer)) {
			Debug::error("{$f} issuer must be a string");
		}
		return $this->tlsIssuer = $issuer;
	}

	public function hasIssuer()
	{
		return isset($this->tlsIssuer);
	}

	public function getIssuer()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getIssuer()";
		if(!$this->hasIssuer()) {
			Debug::error("{$f} issuer is undefined");
		}
		return $this->tlsIssuer;
	}

	public function issuer($issuer)
	{
		$this->setIssuer($issuer);
		return $this;
	}

	public function setSubject($subject)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setSubject()";
		if($subject == null) {
			unset($this->tlsSubject);
			return null;
		}elseif(!is_string($subject)) {
			Debug::error("{$f} subject is undefined");
		}
		return $this->tlsSubject = $subject;
	}

	public function hasSubject()
	{
		return isset($this->tlsSubject);
	}

	public function getSubject()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getSubject()";
		if(!$this->hasSubject()) {
			Debug::error("{$f} subject is undefined");
		}
		return $this->tlsSubject;
	}

	public function subject($subject)
	{
		$this->setSubject($subject);
		return $this;
	}

	public function hasTLSOptions()
	{
		return $this->getSSLFlag() || $this->getX509Flag() || $this->hasCipher() || $this->hasIssuer() || $this->hasSubject();
	}

	public function setMaxQueriesPerHour($count)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setMaxQueriesPerHour()";
		if($count == null) {
			unset($this->maxQueriesPerHourCount);
			return null;
		}elseif(!is_int($count)) {
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1) {
			Debug::error("{$f} input parameter must be positive");
		}
		return $this->maxQueriesPerHourCount = $count;
	}

	public function hasMaxQueriesPerHour()
	{
		return isset($this->maxQueriesPerHourCount);
	}

	public function getMaxQueriesPerHour()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getMaxQueriesPerHour()";
		if(!$this->hasMaxQueriesPerHour()) {
			Debug::error("{$f} undefined");
		}
		return $this->maxQueriesPerHourCount;
	}

	public function maxQueriesPerHour($count)
	{
		$this->setMaxQueriesPerHour($count);
		return $this;
	}

	public function setMaxUpdatesPerHour($count)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setMaxUpdatesPerHour()";
		if($count == null) {
			unset($this->maxUpdatesPerHourCount);
			return null;
		}elseif(!is_int($count)) {
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1) {
			Debug::error("{$f} input parameter must be positive");
		}
		return $this->maxUpdatesPerHourCount = $count;
	}

	public function hasMaxUpdatesPerHour()
	{
		return isset($this->maxUpdatesPerHourCount);
	}

	public function getMaxUpdatesPerHour()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getMaxUpdatesPerHour()";
		if(!$this->hasMaxUpdatesPerHour()) {
			Debug::error("{$f} undefined");
		}
		return $this->maxUpdatesPerHourCount;
	}

	public function maxUpdatesPerHour($count)
	{
		$this->setMaxUpdatesPerHour($count);
		return $this;
	}

	public function setMaxConnectionsPerHour($count)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setMaxConnectionsPerHour()";
		if($count == null) {
			unset($this->maxConnectionsPerHourCount);
			return null;
		}elseif(!is_int($count)) {
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1) {
			Debug::error("{$f} input parameter must be positive");
		}
		return $this->maxConnectionsPerHourCount = $count;
	}

	public function hasMaxConnectionsPerHour()
	{
		return isset($this->maxConnectionsPerHourCount);
	}

	public function getMaxConnectionsPerHour()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getMaxConnectionsPerHour()";
		if(!$this->hasMaxConnectionsPerHour()) {
			Debug::error("{$f} undefined");
		}
		return $this->maxConnectionsPerHourCount;
	}

	public function maxConnectionsPerHour($count)
	{
		$this->setMaxConnectionsPerHour($count);
		return $this;
	}

	public function setMaxUserConnections($count)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setMaxUserConnections()";
		if($count == null) {
			unset($this->maxUserConnectionsCount);
			return null;
		}elseif(!is_int($count)) {
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($count < 1) {
			Debug::error("{$f} input parameter must be positive");
		}
		return $this->maxUserConnectionsCount = $count;
	}

	public function hasMaxUserConnections()
	{
		return isset($this->maxUserConnectionsCount);
	}

	public function getMaxUserConnections()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getMaxUserConnections()";
		if(!$this->hasMaxUserConnections()) {
			Debug::error("{$f} undefined");
		}
		return $this->maxUserConnectionsCount;
	}

	public function maxUserConnections($count)
	{
		$this->setMaxUserConnections($count);
		return $this;
	}

	public function hasResourceOptions()
	{
		return $this->hasMaxQueriesPerHour() || $this->hasMaxUpdatesPerHour() || $this->hasMaxConnectionsPerHour() || $this->hasMaxUserConnections();
	}

	public function setComment($comment)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setComment()";
		try{
			if($comment == null) {
				unset($this->commentString);
				return null;
			}elseif($this->hasAttribute()) {
				Debug::error("{$f} you cannot have both comment and attribute in the same query");
			}elseif(!is_string($comment)) {
				$comment = "{$comment}";
			}
			// $this->setRequiredMySQLVersion("8.0.21");
			return $this->commentString = $comment;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setAttribute($attr)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setAttribute()";
		if($attr == null) {
			unset($this->_attribute);
			return null;
		}elseif($this->hasComment()) {
			Debug::error("{$f} you cannot have both comment and attribute in the same query");
		}elseif(is_array($attr)) {
			$attr = json_encode($attr);
		}
		if(!is_json($attr)) {
			Debug::error("{$f} attribute is not valid JSON");
		}
		// $this->setRequiredMySQLVersion("8.0.21");
		return $this->_attribute = $attr;
	}

	public function hasAttribute()
	{
		return isset($this->_attribute) && is_json($this->_attribute);
	}

	public function getAttribute()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getAttribute()";
		if(!$this->hasAttribute()) {
			Debug::error("{$f} attribute is undefined");
		}
		return $this->_attribute;
	}

	public function setLockOption($opt)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setLockOption()";
		if($opt == null) {
			unset($this->lockOption);
			return null;
		}elseif(!is_string($opt)) {
			Debug::error("{$f} lock option must be a string");
		}
		$opt = strtolower($opt);
		switch ($opt) {
			case LOCK_OPTION_LOCK:
			case LOCK_OPTION_UNLOCK:
				break;
			default:
				Debug::error("{$f} invalid lock option \"{$opt}\"");
		}
		return $this->lockOption = $opt;
	}

	public function accountLock()
	{
		$this->setLockOption(LOCK_OPTION_LOCK);
		return $this;
	}

	public function accountUnlock()
	{
		$this->setLockOption(LOCK_OPTION_UNLOCK);
		return $this;
	}

	public function setPasswordLockTime($n)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setPasswordLockTime()";
		if($n == null) {
			unset($this->passwordLockTimeDays);
			return null;
		}elseif(is_string($n)) {
			$n = strtolower($n);
			if($n !== PASSWORD_OPTION_UNBOUNDED) {
				Debug::error("{$f} invalid password lock option \"{$n}\"");
			}
		}elseif(is_int($n)) {
			if($n < 1) {
				Debug::error("{$f} password lock time must be a positive integer");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		return $this->passwordLockTimeDays = $n;
	}

	public function hasPasswordLockTime()
	{
		return isset($this->passwordLockTimeDays);
	}

	public function getPasswordLockTime()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getPasswordLockTime()";
		if(!$this->hasPasswordLockTime()) {
			Debug::error("{$f} password lock time is undefined");
		}
		return $this->passwordLockTimeDays;
	}

	public function passwordLockTime($n)
	{
		$this->setPasswordLockTime($n);
		return $this;
	}

	public function setFailedLoginAttempts($n)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setFailedLoginAttempts()";
		if($n !== 0 && $n == null) {
			unset($this->failedLoginAttemptsCount);
			return null;
		}elseif(!is_int($n)) {
			Debug::error("{$f} failed login attempt count must be a non-negative integer");
		}elseif($n < 0) {
			Debug::error("{$f} failed login attempt count must be non-negative");
		}
		return $this->failedLoginAttemptsCount = $n;
	}

	public function hasFailedLoginAttempts()
	{
		return isset($this->failedLoginAttemptsCount) && is_int($this->failedLoginAttemptsCount);
	}

	public function getFailedLoginAttempts()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getFailedLoginAttempts()";
		if(!$this->hasFailedLoginAttempts()) {
			Debug::error("{$f} failed login attempt count is undefined");
		}
		return $this->failedLoginAttemptsCount;
	}

	public function failedLoginAttempts($n)
	{
		$this->setFailedLoginAttempts($n);
		return $this;
	}

	public function setRequireCurrentPasswordOption($opt)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setRequireCurrentPasswordOption()";
		if($opt == null) {
			unset($this->requireCurrentPasswordOption);
			return null;
		}elseif(!is_string($opt)) {
			Debug::error("{$f} require current password option must be a string");
		}
		$opt = strtolower($opt);
		switch ($opt) {
			case CONST_DEFAULT:
			case PASSWORD_OPTION_OPTIONAL:
				break;
			default:
				Debug::error("{$f} invalid option \"{$opt}\"");
		}
		return $this->requireCurrentPasswordOption = $opt;
	}

	public function hasRequireCurrentPasswordOption()
	{
		return isset($this->requireCurrentPasswordOption);
	}

	public function getRequireCurrentPasswordOption()
	{
		if(!$this->hasRequireCurrentPasswordOption()) {
			return CONST_DEFAULT;
		}
		return $this->requireCurrentPasswordOption;
	}

	public function passwordRequireCurrent($opt = null)
	{
		if($opt === null) {
			$this->setRequireCurrentPasswordFlag(true);
		}else{
			$this->setRequireCurrentPasswordOption($opt);
		}
		return $this;
	}

	public function setPasswordReuseInterval($count)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setPasswordReuseInterval()";
		if($count == null) {
			unset($this->passwordReuseIntervalOption);
			return null;
		}elseif(is_string($count)) {
			$count = strtolower($count);
			if($count !== CONST_DEFAULT) {
				Debug::error("{$f} invalid password reuse interval option \"{$count}\"");
			}
		}elseif(is_int($count)) {
			if($count < 1) {
				Debug::error("{$f} password reuse interval must be a positive integer");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		return $this->passwordReuseIntervalOption = $count;
	}

	public function hasPasswordReuseInterval()
	{
		return isset($this->passwordReuseIntervalOption);
	}

	public function getPasswordReuseInterval()
	{
		if(!$this->hasPasswordReuseInterval()) {
			return CONST_DEFAULT;
		}
		return $this->passwordReuseIntervalOption;
	}

	public function passwordReuseInterval($count)
	{
		$this->setPasswordReuseInterval($count);
		return $this;
	}

	public function setPasswordHistoryOption($count)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setPasswordHistoryOption()";
		if($count !== 0 && $count == null) {
			unset($this->passwordHistoryOption);
			return null;
		}elseif(is_string($count)) {
			$count = strtolower($count);
			if($count !== CONST_DEFAULT) {
				Debug::error("{$f} invalid password history option \"{$count}\"");
			}
		}elseif(is_int($count)) {
			if($count < 0) {
				Debug::error("{$f} password history count cannot be negative");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		return $this->passwordHistoryOption = $count;
	}

	public function hasPasswordHistoryOption()
	{
		return isset($this->passwordHistoryOption);
	}

	public function getPasswordHistoryOption()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getPasswordHistoryOption()";
		if(!$this->hasPasswordHistory()) {
			Debug::error("{$f} password history is undefined");
		}
		return $this->passwordHistoryOption;
	}

	public function passwordHistory($count)
	{
		$this->setPasswordHistoryOption($count);
		return $this;
	}

	public function setPasswordExpiration($interval)
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->setPasswordExpiration()";
		if($interval == null) {
			unset($this->passwordExpires);
			return null;
		}elseif(is_string($interval)) {
			$interval = strtolower($interval);
			switch ($interval) {
				case CONST_DEFAULT:
				case PASSWORD_OPTION_NEVER:
					break;
				default:
					Debug::error("{$f} invalid password expiration string \"{$interval}\"");
			}
		}elseif(is_int($interval)) {
			if($interval < 1) {
				Debug::error("{$f} interval must be a positive integer");
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		return $this->passwordExpires = $interval;
	}

	public function hasPasswordExpiration()
	{
		return isset($this->passwordExpires);
	}

	public function getPasswordExpiration()
	{
		if(!$this->hasPasswordExpiration()) {
			return CONST_DEFAULT;
		}
		return $this->passwordExpires;
	}

	public function passwordExpire($interval = null)
	{
		if($interval === null) {
			$this->setPasswordExpiredFlag(true);
		}else{
			$this->setPasswordExpiration($interval);
		}
		return $this;
	}

	protected function getTLSOptionsString()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getTLSOptionsString()";
		$string = "require";
		if($this->getRequireNoneFlag()) {
			$string .= " none";
		}elseif($this->hasTLSOptions()) {
			if($this->getSSLFlag()) {
				$string .= " SSL";
			}
			if($this->getX509Flag()) {
				$string .= " X509";
			}
			if($this->hasCipher()) {
				$string .= " cipher '" . escape_quotes($this->getCipher(), QUOTE_STYLE_SINGLE) . "'";
			}
			if($this->hasIssuer()) {
				$string .= " issuer '" . escape_quotes($this->getIssuer(), QUOTE_STYLE_SINGLE) . "'";
			}
			if($this->hasSubject()) {
				$string .= " subject '" . escape_quotes($this->getSubject(), QUOTE_STYLE_SINGLE) . "'";
			}
		}else{
			Debug::error("{$f} neither of the above");
		}
		return $string;
	}

	protected function getResourceOptionsString()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getResourceOptionsString()";
		if(!$this->hasResourceOptions()) {
			Debug::error("{$f} resource options are undefined");
		}
		$string = " with ";
		// MAX_QUERIES_PER_HOUR count
		if($this->hasMaxQueriesPerHour()) {
			$string .= "max_queries_per_hour " . $this->getMaxQueriesPerHour();
		}
		// | MAX_UPDATES_PER_HOUR count
		if($this->hasMaxUpdatesPerHour()) {
			$string .= "max_updates_per_hour " . $this->getMaxUpdatesPerHour();
		}
		// | MAX_CONNECTIONS_PER_HOUR count
		if($this->hasMaxConnectionsPerHour()) {
			$string .= "max_connections_per_hour " . $this->getMaxConnectionsPerHour();
		}
		// | MAX_USER_CONNECTIONS count
		if($this->hasMaxUserConnections()) {
			$string .= "max_user_connection " . $this->getMaxUserConnections();
		}
		return $string;
	}

	public function hasPasswordOptions()
	{
		return $this->hasPasswordExpiration() || $this->getPasswordExpiredFlag() || $this->hasPasswordHistoryOption() || $this->hasPasswordReuseInterval() || $this->getRequireCurrentPasswordFlag() || $this->hasRequireCurrentPasswordOption() || $this->hasLockOption() || ($this->hasFailedLoginAttempts() && $this->hasPasswordLockTime() && hasMinimumMySQLVersion("8.0.19"));
	}

	protected function getPasswordOptionsString()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getPasswordOptionsString()";
		if(!$this->hasPasswordOptions()) {
			Debug::error("{$f} password options are undefined");
		}
		$string = "";
		// PASSWORD EXPIRE [DEFAULT | NEVER | INTERVAL N DAY]
		if($this->hasPasswordExpiration() || $this->getPasswordExpiredFlag()) {
			$string .= " password expire";
			if(!$this->getPasswordExpiredFlag() && $this->hasPasswordExpiration()) {
				$string .= " ";
				$interval = $this->getPasswordExpiration();
				if(is_int($interval)) {
					$string .= "interval {$interval} day";
				}elseif(is_string($interval)) {
					$string .= $interval;
				}else{
					Debug::error("{$f} neither of the above");
				}
			}
		}
		// | PASSWORD HISTORY {DEFAULT | N}
		if($this->hasPasswordHistoryOption()) {
			$string .= " password history " . $this->getPasswordHistoryOption();
		}
		// | PASSWORD REUSE INTERVAL {DEFAULT | N DAY}
		if($this->hasPasswordReuseInterval()) {
			$interval = $this->getPasswordReuseInterval();
			$string .= " password reuse interval {$interval}";
			if(is_int($interval)) {
				$string .= " day";
			}
		}
		// | PASSWORD REQUIRE CURRENT [DEFAULT | OPTIONAL]
		if($this->getRequireCurrentPasswordFlag() || $this->hasRequireCurrentPasswordOption()) {
			$string .= " password require current";
			if($this->hasRequireCurrentPasswordOption()) {
				$string .= " " . $this->getRequireCurrentPasswordOption();
			}
		}
		if($this->hasFailedLoginAttempts() && $this->hasPasswordLockTime() && hasMinimumMySQLVersion("8.0.19")) {
			// | FAILED_LOGIN_ATTEMPTS N
			$string .= " failed_login_attempts " . $this->getFailedLoginAttempts();
			// | PASSWORD_LOCK_TIME {N | UNBOUNDED}
			$string .= " password_lock_time " . $this->getPasswordLockTime();
		}
		// [{ ACCOUNT LOCK | ACCOUNT UNLOCK }]
		if($this->hasLockOption()) {
			$string .= " account " . $this->getLockOption();
		}
		return $string;
	}

	protected function getCommentAttributeString()
	{
		$f = __METHOD__; //UserStatement::getShortClass()."(".static::getShortClass().")->getCommentAttributeString()";
		if(! hasMinimumMySQLVersion("8.0.21")) {
			Debug::error("{$f} insufficient MySQL version");
		}
		if($this->hasComment()) {
			return " comment '" . escape_quotes($this->getComment(), QUOTE_STYLE_SINGLE) . "'";
		}elseif($this->hasAttribute()) {
			$attr = escape_quotes($this->getAttribute(), QUOTE_STYLE_SINGLE);
			return " attribute '{$attr}'";
		}
		Debug::error("{$f} comment and attribute are undefined");
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->_attribute);
		unset($this->commentString);
		unset($this->failedLoginAttemptsCount);
		unset($this->lockOption);
		unset($this->maxConnectionsPerHourCount);
		unset($this->maxQueriesPerHourCount);
		unset($this->maxUpdatesPerHourCount);
		unset($this->maxUserConnectionsCount);
		unset($this->passwordExpires);
		unset($this->passwordHistoryOption);
		unset($this->passwordLockTimeDays);
		unset($this->requireCurrentPasswordOption);
		unset($this->passwordReuseIntervalOption);
		unset($this->tlsCipher);
		unset($this->tlsIssuer);
		unset($this->tlsSubject);
	}
}
