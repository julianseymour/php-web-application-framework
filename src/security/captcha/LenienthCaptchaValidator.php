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

class LenienthCaptchaValidator extends hCaptchaValidator
{

	protected $allowedStrikes;

	protected $requestAttemptClass;

	public function __construct($attemptClass = LoginAttempt::class)
	{ // , $allowedStrikes=null){
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		if (! empty($attemptClass)) {
			$this->setAccessAttemptClass($attemptClass);
		}
		/*
		 * if($allowedStrikes === null){
		 * $allowedStrikes = 1;
		 * }
		 */
		$this->setAllowedStrikes(1); // $allowedStrikes);
	}

	public function setAccessAttemptClass($class)
	{
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->setAccessAttemptClass({$class})";
		if (! is_string($class)) {
			Debug::error("{$f} request attempt class \"{$class}\" is not a string");
		} elseif (class_exists($class)) {
			return $this->requestAttemptClass = $class;
		}
		Debug::error("{$f} request attempt class \"{$class}\" does not exist");
	}

	public function hasAccessAttemptClass()
	{
		return ! empty($this->requestAttemptClass) && is_string($this->requestAttemptClass) && class_exists($this->requestAttemptClass);
	}

	public function getAccessAttemptClass()
	{
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->getAccessAttemptClass()";
		if (! $this->hasAccessAttemptClass()) {
			Debug::error("{$f} request attempt class is undefined");
		}
		return $this->requestAttemptClass;
	}

	public function setAllowedStrikes($strikes)
	{
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->setAllowedStrikes({$strikes})";
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

	public function hasAllowedStrikes()
	{
		return isset($this->allowedStrikes) && is_int($this->allowedStrikes);
	}

	public function getAllowedStrikes()
	{
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->getAllowedStrikes()";
		if (! $this->hasAllowedStrikes()) {
			Debug::error("{$f} allowed strike count is undefined");
		}
		return $this->allowedStrikes;
	}

	public function getFailedRequestCount($mysqli)
	{
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->getFailedRequestCount()";
		try {
			$print = false;
			if (cache()->enabled() && USER_CACHE_ENABLED) {
				$key = $this->getAccessAttemptClass()::getCacheKeyFromIpAddress($_SERVER['REMOTE_ADDR']);
				if (cache()->hasAPCu($key)) {
					return $this->getAllowedStrikes() + 1;
				} else {
					return 0;
				}
			} elseif ($print) {
				Debug::print("{$f} cache is disabled");
			}
			return $this->getAccessAttemptClass()::selectStatic(null, "insertIpAddress", "insertTimestamp", "loginSuccessful")->where(new AndCommand(new WhereCondition("insertIpAddress", OPERATOR_EQUALS), new WhereCondition("insertTimestamp", OPERATOR_GREATERTHANEQUALS), new WhereCondition("loginSuccessful", OPERATOR_EQUALS)))->prepareBindExecuteGetResultCount($mysqli, 'sii', $_SERVER['REMOTE_ADDR'], time() - LOCKOUT_DURATION, FAILURE);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function validateFailedRequestCount($mysqli)
	{
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->validateFailedRequestCount()";
		try {
			$count = $this->getFailedRequestCount($mysqli);
			if ($count <= $this->getAllowedStrikes()) {
				// Debug::print("{$f} count {$count} is within allowed quota {$this->allowedStrikes}");
				return SUCCESS;
			}
			// Debug::warning("{$f} count {$count} exceeds quota {$this->allowedStrikes}");
			return FAILURE;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //LenienthCaptchaValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
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
