<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\access;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubtypeColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressEmail;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;

abstract class AccessAttempt extends RequestEvent implements StaticSubtypeInterface, StaticTableNameInterface{

	use StaticTableNameTrait;
	use SubtypeColumnTrait;
	
	protected $authenticatedUserClass;
	
	public static final function getAccessTypeStatic(): string{
		return static::getSubtypeStatic();
	}

	public function getSubtype():string{
		if($this->hasColumnValue('subtype')){
			return $this->getColumnValue('subtype');
		}
		return $this->setSubtype(static::getSubtypeStatic());
	}

	public static function getSuccessfulResultCode():int{
		return FAILURE;
	}

	public static function getEmailNotificationClass():?string{
		return UnlistedIpAddressEmail::class;
	}

	public static final function getDataType(): string{
		return DATATYPE_ACCESS_ATTEMPT;
	}

	public function bruteforceProtection(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->hasUserData()){
				Debug::error("{$f} true client object is undefined");
			}
			// 1. bad apple: if this IP address has made recent attempts on multiple accounts
			$count = $this->getMultipleUserAttemptCount($mysqli);
			if($count > MAX_FAILED_WAIVERS_BY_USER_KEY){
				if($print){
					Debug::print("{$f} BFP failed by user ID");
				}
				return $this->setLoginResult(RESULT_BFP_WAIVER_FAILED_MULTIPLE);
			}
			// 2. bad apple: if this IP address has already attempted and failed too many times for this account,
			$count = $this->getFailedAttemptCount($mysqli);
			if($count > MAX_FAILED_LOGINS_BY_IP){
				if($print){
					Debug::print("{$f} BFP failed by IP address");
				}
				return $this->setLoginResult(RESULT_BFP_IP_LOCKOUT_START);
			}
			return SUCCESS; // do not set result here -- it can still fail, and this will prematurely mark it as successful
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getCidrNotation(){
		$f = __METHOD__;
		$ip = $_SERVER['REMOTE_ADDR'];
		if(preg_match(REGEX_IPv4_ADDRESS, $ip)){
			return "{$ip}/32";
		}elseif(preg_match(REGEX_IPv6_ADDRESS, $ip)){
			return "{$ip}/128";
		}
		Debug::error("{$f} this application only supports IP versions 4 and 6");
	}

	public function getMultipleUserAttemptCount(mysqli $mysqli){
		$f = __METHOD__;
		try{
			if(!$this->hasUserKey()){
				Debug::error("{$f} user data is undefined");
			}
			$orderby = new OrderByClause("insertTimestamp", DIRECTION_DESCENDING);
			$where2 = new WhereCondition('insertIpAddress', OPERATOR_EQUALS);
			$where3 = new WhereCondition("insertTimestamp", OPERATOR_GREATERTHAN);
			$select = static::selectStatic(null, "insertTimestamp")->where(
				new AndCommand(
					new WhereCondition(
						'uniqueKey',
						OPERATOR_IN,
						null,
						static::selectStatic(null, 'uniqueKey')->where(
							$this->whereIntersectionalHostKey(
								config()->getNormalUserClass(),
								"userKey",
								OPERATOR_LESSTHANGREATERTHAN
							)
						)
					),
					$where2,
					$where3
				)
			)->orderBy($orderby)->limit(5)->union(
				static::selectStatic(null, "insertTimestamp")->where(
					new AndCommand(
						new WhereCondition(
							'uniqueKey',
							OPERATOR_IN,
							null,
							static::selectStatic(null, 'uniqueKey')->where(
								$this->whereIntersectionalHostKey(
									config()->getAdministratorClass(),
									"userKey",
									OPERATOR_LESSTHANGREATERTHAN
								)
							)
						),
						$where2,
						$where3
					)
				)->orderBy($orderby)->limit(5)
			);
			$count = $select->prepareBindExecuteGetResultCount(
				$mysqli,
				'sssisssi',
				$this->getUserKey(),
				"userKey",
				$_SERVER['REMOTE_ADDR'],
				$this->getExpiredTimestamp(),
				$this->getUserKey(),
				"userKey",
				$_SERVER['REMOTE_ADDR'],
				$this->getExpiredTimestamp()
			);
			deallocate($select);
			return $count;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setAuthenticatedUserClass($class){
		return $this->authenticatedUserClass = $class;
	}

	public function getAuthenticatedUserClass():?string{
		return $this->authenticatedUserClass;
	}

	public function getLoginSuccessful(){
		$f = __METHOD__;
		try{
			$field = 'loginSuccessful';
			$success = $this->getColumnValue($field);
			if($success === null){
				// Debug::print("{$f} success flag is super null, about to call wasLoginSuccessful");
				$value = $this->wasLoginSuccessful();
				return $this->setColumnValue($field, $value);
			}
			// Debug::print("{$f} returning normally");
			return $success;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setLoginSuccessful($success){
		return $this->setColumnValue('loginSuccessful', $success);
	}

	protected static function wasLoginSuccessfulStatic($result){ // XXX refactor to LoginAttempt
		$f = __METHOD__;
		try{
			// $result = $this->getLoginResult();
			if($result === null || $result === ""){
				Debug::error("{$f} login result is empty");
			}
			$haystack = [
				RESULT_BFP_MFA_CONFIRM,
				SUCCESS
			];
			if(false === array_search($result, $haystack)){
				Debug::warning("{$f} no, this login with result \"{$result}\" was not successful");
				return false; // $this->setLoginSuccessful(FAILURE);
			}
			// Debug::print("{$f} yes, this login was successful");
			return true;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function wasLoginSuccessful(){
		$f = __METHOD__;
		if($this->hasColumnValue("loginSuccessful")){
			return $this->getColumnValue('loginSuccessful');
		}
		$result = $this->getLoginResult();
		$success = $this->wasLoginSuccessfulStatic($result);
		return $this->setLoginSuccessful($success);
	}

	public function setLoginResult($result){
		$f = __METHOD__;
		if($result === FAILURE){
			Debug::error("{$f} not specific enough");
		}
		$this->setColumnValue('loginResult', $result);
		$this->setLoginSuccessful($this->wasLoginSuccessfulStatic($result));
		return $this->getLoginResult();
	}

	public function getLoginResult(){
		return $this->getColumnValue('loginResult');
	}

	public function hasLoginResult():bool{
		return $this->hasColumnValue("loginResult");
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$success = new BooleanDatum("loginSuccessful");
		$login_result = new UnsignedIntegerDatum("loginResult", 16);
		/*if($ds->getDebugFlag()){
			$ds->debug(false);
			$login_result->debug();
		}*/
		array_push($columns, $login_result, $success);
	}

	public function getFailedAttemptCount(mysqli $mysqli){
		$f = __METHOD__;
		try{
			$select = $this->select("insertTimestamp")->where(new AndCommand(new WhereCondition('insertIpAddress', OPERATOR_EQUALS), new WhereCondition('loginSuccessful', OPERATOR_EQUALS), new WhereCondition('insertTimestamp', OPERATOR_GREATERTHAN)))->orderBy(new OrderByClause("insertTimestamp", DIRECTION_DESCENDING))->limit(MAX_FAILED_WAIVERS_BY_IP + 1)->withTypeSpecifier('sii')->withParameters([
				$_SERVER['REMOTE_ADDR'],
				FAILURE,
				$this->getExpiredTimestamp()
			]);
			$count = $select->executeGetResultCount($mysqli);
			deallocate($select);
			return $count;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function initializeAccessAttempt(mysqli $mysqli){
		$f = __METHOD__;try{
			$this->generateInsertTimestamp();
			$this->setInsertIpAddress($_SERVER['REMOTE_ADDR']);
			$this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			$status = $this->getObjectStatus();
			if($status !== SUCCESS && $status !== STATUS_UNINITIALIZED){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// Debug::print("{$f} returning normally");
			return $this->setObjectStatus(SUCCESS);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getPhylumName(): string{
		return "access_attempts";
	}

	public static function getReasonLoggedStatic(){
		return CONST_UNDEFINED;
	}

	public static function getPrettyClassName():string{
		return _("Access attempt");
	}

	public static function getPrettyClassNames():string{
		return _("Access attempts");
	}

	public function isSecurityNotificationWarranted():bool{
		return true;
	}

	public static function getTableNameStatic(): string{
		return "access_attempts";
	}
	
	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		try{
			switch($column_name){
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
			case 'subtype':
				return true;
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}
}
	