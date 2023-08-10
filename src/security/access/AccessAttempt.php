<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\access;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressEmail;
use Exception;
use mysqli;

class AccessAttempt extends RequestEvent
{

	protected $authenticatedUserClass;

	public static function getAccessTypeStatic(): string
	{
		return CONST_UNDEFINED;
	}

	public static function getSubtypeStatic(): string
	{
		return static::getAccessTypeStatic();
	}

	public static function getSuccessfulResultCode()
	{
		return FAILURE;
	}

	public static function getEmailNotificationClass()
	{
		return UnlistedIpAddressEmail::class;
	}

	public static final function getDataType(): string
	{
		return DATATYPE_ACCESS_ATTEMPT;
	}

	public function bruteforceProtection(mysqli $mysqli): int
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->bruteforceProtection()";
		try {
			if (! $this->hasUserData()) {
				Debug::error("{$f} true client object is undefined");
			}
			// 1. bad apple: if this IP address has made recent attempts on multiple accounts
			/*
			 * $count = $this->getMultipleUserAttemptCount($mysqli);
			 * if($count > MAX_FAILED_WAIVERS_BY_USER_KEY){
			 * return $this->setLoginResult(RESULT_BFP_WAIVER_FAILED_MULTIPLE);
			 * }
			 */
			// 2. bad apple: if this IP address has already attempted and failed too many times for this account,
			$count = $this->getFailedAttemptCount($mysqli);
			if ($count > MAX_FAILED_LOGINS_BY_IP) {
				return $this->setLoginResult(RESULT_BFP_IP_LOCKOUT_START);
			}
			return SUCCESS; // do not set result here -- it can still fail, and this will prematurely mark it as successful
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public final function hasSubtypeValue(): bool
	{
		return true;
	}

	public final function getSubtypeValue(): string
	{
		return $this->getAccessTypeStatic();
	}

	public function getCidrNotation()
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->getCidrNotation()";
		$ip = $_SERVER['REMOTE_ADDR'];
		if (preg_match(REGEX_IPv4_ADDRESS, $ip)) {
			return "{$ip}/32";
		} elseif (preg_match(REGEX_IPv6_ADDRESS, $ip)) {
			return "{$ip}/128";
		}
		Debug::error("{$f} this application only supports IP versions 4 and 6");
	}

	// XXX TODO left off here -- select from the intersection table instead
	public function getMultipleUserAttemptCount($mysqli){
		$f = __METHOD__;
		try {
			if (! $this->hasUserKey()) {
				Debug::error("{$f} user data is undefined");
			}
			$orderby = new OrderByClause("insertTimestamp", DIRECTION_DESCENDING);
			$where2 = new WhereCondition('insertIpAddress', OPERATOR_EQUALS);
			$where3 = new WhereCondition("insertTimestamp", OPERATOR_GREATERTHAN);
			return static::selectStatic(null, "insertTimestamp")->where(new AndCommand(new WhereCondition('uniqueKey', OPERATOR_IN, null, static::selectStatic(null, 'uniqueKey')->where($this->whereIntersectionalHostKey(config()->getNormalUserClass(), "userKey", OPERATOR_LESSTHANGREATERTHAN))), $where2, $where3))->orderBy($orderby)->limit(5)->union(static::selectStatic(null, "insertTimestamp")->where(new AndCommand(new WhereCondition('uniqueKey', OPERATOR_IN, null, static::selectStatic(null, 'uniqueKey')->where($this->whereIntersectionalHostKey(config()->getAdministratorClass(), "userKey", OPERATOR_LESSTHANGREATERTHAN))), $where2, $where3))->orderBy($orderby)->limit(5))->prepareBindExecuteGetResultCount($mysqli, 'sssisssi', $this->getUserKey(), "userKey", $_SERVER['REMOTE_ADDR'], $this->getExpiredTimestamp(), $this->getUserKey(), "userKey", $_SERVER['REMOTE_ADDR'], $this->getExpiredTimestamp());
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setAuthenticatedUserClass($class){
		return $this->authenticatedUserClass = $class;
	}

	public function getAuthenticatedUserClass(){
		return $this->authenticatedUserClass;
	}

	public function getLoginSuccessful(){
		$f = __METHOD__;
		try {
			$field = 'loginSuccessful';
			$success = $this->getColumnValue($field);
			if ($success === null) {
				// Debug::print("{$f} success flag is super null, about to call wasLoginSuccessful");
				$value = $this->wasLoginSuccessful();
				return $this->setColumnValue($field, $value);
			}
			// Debug::print("{$f} returning normally");
			return $success;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasMasterAccountKey()
	{
		return $this->hasUserMasterAccountKey();
	}

	public function hasUserMasterAccountKey()
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->hasUserMasterAccountKey()";
		try {
			// Debug::print("{$f} entered");
			if (! $this->hasUserData()) {
				Debug::error("{$f} true client object is undefined");
			}
			$tco = $this->getUserData();
			if ($tco == null) {
				Debug::error("{$f} true client object returned null");
				$this->setObjectStatus(ERROR_NULL_TRUE_USER_OBJECT);
			}
			$has = $tco->hasMasterAccountKey();
			if ($has) {
				// Debug::print("{$f} yes, the true client object has its master account key defined");
			} else {
				Debug::warning("{$f} no, the true client object does not have a defined master account key");
			}
			// Debug::print("{$f} returning normally");
			return $has;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getMasterAccountKey()
	{
		return $this->getUserMasterAccountKey();
	}

	public function getUserMasterAccountKey()
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->getUserMasterAccountKey()";
		try {
			// Debug::print("{$f} entered");
			if (! $this->hasUserData()) {
				Debug::error("{$f} true client object is undefined");
			}
			$key = $this->getUserData()->getMasterAccountKey();
			// Debug::print("{$f} returning \"{$key}\"");
			return $key;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setLoginSuccessful($success)
	{
		return $this->setColumnValue('loginSuccessful', $success);
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @return AuthenticatedUser
	 */
	/*
	 * public function acquireLoginTestedUserData($mysqli):?AuthenticatedUser{
	 * $f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->acquireLoginTestedUserData()";
	 * try{
	 * $print = false;
	 * if($mysqli == null){
	 * $status = ERROR_MYSQL_CONNECT;
	 * $err = ErrorMessage::getResultMessage($status);
	 * Debug::error("{$f} {$err}");
	 * return null;
	 * }elseif($print){
	 * Debug::print("{$f} entered");
	 * }
	 * $user_class = $this->getAuthenticatedUserClass();
	 * if(empty($user_class)){
	 * Debug::error("{$f} authenticated user class returned empty string");
	 * }elseif($print){
	 * Debug::print("{$f} about to call authenticated client class {$user_class}'s static test login function");
	 * }
	 * if(!hasInputParameter("name")){
	 * Debug::warning("{$f} name is null");
	 * $this->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
	 * return null;
	 * }elseif(!hasInputParameter("password")){
	 * Debug::warning("{$f} posted password is null");
	 * $this->setObjectStatus(ERROR_PASSWORD_UNDEFINED);
	 * return null;
	 * }
	 * $name = getInputParameter("name");
	 * $password = getInputParameter('password');
	 * $normalized = NameDatum::normalize($name);
	 * $user = new $user_class(ALLOCATION_MODE_SUBJECTIVE);
	 * $select = $user->getNormalizedNameSelectStatement($normalized);
	 * if($print){
	 * Debug::print("{$f} query for selecting user by normalized name: {$select}");
	 * }
	 * $result = $select->executeGetResult($mysqli);
	 * $results = $result->fetch_all(MYSQLI_ASSOC);
	 * $result->free_result();
	 * $count = count($results);
	 * if($count === 0){
	 * if($print){
	 * Debug::print("{$f} no results");
	 * }
	 * $user->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
	 * return $user;
	 * }elseif($count > 1){
	 * Debug::error("{$f} {$count} results");
	 * }
	 * $status = $user->processQueryResultArray($mysqli, $results[0]);
	 * if($status === ERROR_NOT_FOUND){
	 * Debug::warning("{$f} user not found");
	 * $user->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
	 * return $user;
	 * }
	 * $hash = $user->getPasswordHash();
	 * if(password_verify($password, $hash)){
	 * if($print){
	 * Debug::print("{$f} password verification successful");
	 * }
	 * $user->setObjectStatus(SUCCESS);
	 * }else{
	 * if($print){
	 * Debug::print("{$f} password verification failed");
	 * }
	 * $user->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
	 * }
	 * return $user;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */

	/*
	 * public function executeLoginAttempt($mysqli):int{
	 * $f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")::executeLoginAttempt()";
	 * try{
	 * $print = false;
	 * //preemptive error checking
	 * $user_class = $this->getAuthenticatedUserClass();
	 * if(empty($user_class)){
	 * Debug::error("{$f} authenticated user class returned empty string");
	 * }elseif($print){
	 * Debug::print("{$f} about to call authenticated client class {$user_class}'s static test login function");
	 * }
	 * if(!hasInputParameter("name")){
	 * Debug::warning("{$f} name is null");
	 * return $this->setLoginResult($this->setObjectStatus(ERROR_LOGIN_CREDENTIALS));
	 * }elseif(!hasInputParameter("password")){
	 * Debug::warning("{$f} posted password is null");
	 * return $this->setLoginResult($this->setObjectStatus(ERROR_PASSWORD_UNDEFINED));
	 * }
	 * //load user by normalized name
	 * $user = new $user_class(ALLOCATION_MODE_SUBJECTIVE);
	 * $select = $user->getNormalizedNameSelectStatement(
	 * NameDatum::normalize(getInputParameter("name"))
	 * );
	 * if($print){
	 * Debug::print("{$f} query for selecting user by normalized name: {$select}");
	 * }
	 * $result = $select->executeGetResult($mysqli);
	 * $results = $result->fetch_all(MYSQLI_ASSOC);
	 * $result->free_result();
	 * $count = count($results);
	 * if($count === 0){
	 * if($print){
	 * Debug::print("{$f} no results");
	 * }
	 * return $this->setLoginResult($user->setObjectStatus(ERROR_LOGIN_CREDENTIALS));
	 * }elseif($count > 1){
	 * Debug::error("{$f} {$count} results");
	 * }
	 * $status = $user->processQueryResultArray($mysqli, $results[0]);
	 * if($status === ERROR_NOT_FOUND){
	 * Debug::warning("{$f} user not found");
	 * return $this->setLoginResult($user->setObjectStatus(ERROR_LOGIN_CREDENTIALS));
	 * }
	 * //validate password hash
	 * if(password_verify(getInputParameter('password'), $user->getPasswordHash())){
	 * if($print){
	 * Debug::print("{$f} password verification successful");
	 * }
	 * $user->setObjectStatus(SUCCESS);
	 * }else{
	 * if($print){
	 * Debug::print("{$f} password verification failed");
	 * }
	 * $user->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
	 * }
	 * return $this->setLoginResult($user->getObjectStatus());
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	protected static function wasLoginSuccessfulStatic($result)
	{ // XXX refactor to LoginAttempt
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")::wasLoginSuccessfulStatic()";
		try {
			// $result = $this->getLoginResult();
			if ($result === null || $result === "") {
				Debug::error("{$f} login result is empty");
			}
			$haystack = [
				RESULT_BFP_MFA_CONFIRM,
				SUCCESS
			];
			if (false === array_search($result, $haystack)) {
				Debug::warning("{$f} no, this login with result \"{$result}\" was not successful");
				return false; // $this->setLoginSuccessful(FAILURE);
			}
			// Debug::print("{$f} yes, this login was successful");
			return true;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function wasLoginSuccessful()
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->wasLoginSuccessful()";
		if ($this->hasColumnValue("loginSuccessful")) {
			return $this->getColumnValue('loginSuccessful');
		}
		$result = $this->getLoginResult();
		$success = $this->wasLoginSuccessfulStatic($result);
		return $this->setLoginSuccessful($success);
	}

	public function setLoginResult($result)
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->setLoginResult()";
		if ($result === FAILURE) {
			Debug::error("{$f} not specific enough");
		}
		$this->setColumnValue('loginResult', $result);
		$this->setLoginSuccessful($this->wasLoginSuccessfulStatic($result));
		return $this->getLoginResult();
	}

	public function getLoginResult()
	{
		return $this->getColumnValue('loginResult');
	}

	public function hasLoginResult()
	{
		return $this->hasColumnValue("loginResult");
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")::declareColumns()";
		parent::declareColumns($columns, $ds);
		$success = new BooleanDatum("loginSuccessful");
		$login_result = new UnsignedIntegerDatum("loginResult", 16);
		static::pushTemporaryColumnsStatic($columns, $login_result, $success);
	}

	public function getFailedAttemptCount($mysqli)
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->getFailedAttemptCount()";
		try {
			return $this->select("insertTimestamp")
				->where(new AndCommand(new WhereCondition('insertIpAddress', OPERATOR_EQUALS), new WhereCondition('loginSuccessful', OPERATOR_EQUALS), new WhereCondition('insertTimestamp', OPERATOR_GREATERTHAN)))
				->orderBy(new OrderByClause("insertTimestamp", DIRECTION_DESCENDING))
				->limit(MAX_FAILED_WAIVERS_BY_IP + 1)
				->withTypeSpecifier('sii')
				->withParameters([
				$_SERVER['REMOTE_ADDR'],
				FAILURE,
				$this->getExpiredTimestamp()
			])
				->executeGetResultCount($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function initializeAccessAttempt($mysqli)
	{
		$f = __METHOD__; //AccessAttempt::getShortClass()."(".static::getShortClass().")->initializeAccessAttempt()";
		try {
			$this->generateInsertTimestamp();
			$this->setInsertIpAddress($_SERVER['REMOTE_ADDR']);
			$this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			$status = $this->getObjectStatus();
			if ($status !== SUCCESS && $status !== ERROR_UNINITIALIZED) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// Debug::print("{$f} returning normally");
			return $this->setObjectStatus(SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPhylumName(): string
	{
		return "access_attempts";
	}

	public static function getIpLogReason()
	{
		return CONST_UNDEFINED;
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return "Access attempt";
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return "Access attempts";
	}

	public function isSecurityNotificationWarranted()
	{
		return true;
	}

	public static function getTableNameStatic(): string
	{
		return "access_attempts";
	}
}
	