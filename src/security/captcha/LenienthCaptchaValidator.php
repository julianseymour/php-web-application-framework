<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\captcha;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;
use function JulianSeymour\PHPWebApplicationFramework\getTimeStringFromTimestamp;

class LenienthCaptchaValidator extends hCaptchaValidator{

	protected $allowedStrikes;

	protected $requestAttemptClass;

	public function __construct($attemptClass = LoginAttempt::class){
		$f = __METHOD__;
		parent::__construct();
		if (! empty($attemptClass)) {
			$this->setAccessAttemptClass($attemptClass);
		}
		$this->setAllowedStrikes(1);
	}

	public function setAccessAttemptClass(?string $class):?string{
		$f = __METHOD__;
		if (! is_string($class)) {
			Debug::error("{$f} request attempt class \"{$class}\" is not a string");
		} elseif (class_exists($class)) {
			return $this->requestAttemptClass = $class;
		}
		Debug::error("{$f} request attempt class \"{$class}\" does not exist");
	}

	public function hasAccessAttemptClass():bool{
		return ! empty($this->requestAttemptClass) && is_string($this->requestAttemptClass) && class_exists($this->requestAttemptClass);
	}

	public function getAccessAttemptClass():string{
		$f = __METHOD__;
		if (! $this->hasAccessAttemptClass()) {
			Debug::error("{$f} request attempt class is undefined");
		}
		return $this->requestAttemptClass;
	}

	public function setAllowedStrikes(?int $strikes):?int{
		$f = __METHOD__;
		if (is_array($strikes)) {
			Debug::error("{$f} strike count is an array");
		} elseif (is_object($strikes)) {
			Debug::error("{$f} strike count is an object");
		} elseif (! is_int($strikes)) {
			Debug::error("{$f} strike count \"{$strikes}\" is not an integer");
		} elseif ($strikes < 0) {
			Debug::error("{$f} strike count is negative");
		}
		return $this->allowedStrikes = $strikes;
	}

	public function hasAllowedStrikes():bool{
		return isset($this->allowedStrikes) && is_int($this->allowedStrikes);
	}

	public function getAllowedStrikes():int{
		$f = __METHOD__;
		if (! $this->hasAllowedStrikes()) {
			Debug::error("{$f} allowed strike count is undefined");
		}
		return $this->allowedStrikes;
	}

	public function getFailedRequestCount(mysqli $mysqli):int{
		$f = __METHOD__;
		try {
			$print = false;
			if (false && cache()->enabled() && USER_CACHE_ENABLED) {
				$key = $this->getAccessAttemptClass()::getCacheKeyFromIpAddress($_SERVER['REMOTE_ADDR']);
				if (cache()->hasAPCu($key)) {
					return $this->getAllowedStrikes() + 1;
				} else {
					if($print){
						Debug::print("{$f} nothing was cached for this IP address");
					}
					return 0;
				}
			} elseif ($print) {
				Debug::print("{$f} cache is disabled");
			}
			$ts = time() - LOCKOUT_DURATION;
			$select = $this->getAccessAttemptClass()::selectStatic(
				null, "insertIpAddress", "insertTimestamp", "loginSuccessful"
			)->where(
				new AndCommand(
					new WhereCondition("insertIpAddress", OPERATOR_EQUALS),
					new WhereCondition("insertTimestamp", OPERATOR_GREATERTHANEQUALS),
					new WhereCondition("loginSuccessful", OPERATOR_EQUALS)
				)
			);
			$count = $select->prepareBindExecuteGetResultCount(
				$mysqli, 
				'sii', 
				$_SERVER['REMOTE_ADDR'], 
				$ts, 
				FAILURE
			);
			if($print){
				Debug::print("{$f} returning count {$count} failed logins since ".getTimeStringFromTimestamp($ts));
			}
			return $count;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function validateFailedRequestCount(mysqli $mysqli):int{
		$f = __METHOD__;
		try {
			$print = false;
			$count = $this->getFailedRequestCount($mysqli);
			if ($count <= $this->getAllowedStrikes()) {
				if($print){
					Debug::print("{$f} count {$count} is within allowed quota {$this->allowedStrikes}");
				}
				return SUCCESS;
			}elseif($print){
				Debug::warning("{$f} count {$count} exceeds quota {$this->allowedStrikes}");
			}
			return FAILURE;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function evaluate(&$validate_me): int{
		$f = __METHOD__;
		try {
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if ($this->validateFailedRequestCount($mysqli) === SUCCESS) {
				return SUCCESS;
			}
			return parent::evaluate($validate_me);
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
