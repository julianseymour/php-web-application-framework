<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\account\lockout\LockoutConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\command\NoOpCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\InsertBeforeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\LenienthCaptchaValidator;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptcha;
use Exception;
use mysqli;

class LoginAttempt extends AccessAttempt{

	public static function skipUserAcquisitionOnLoad():bool{
		return true;
	}

	public static function getPhylumName(): string{
		return "loginAttempts";
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_INSERT:
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public static function getCacheKeyFromIpAddress(string $ip_address): string{
		return "failedLogin-{$ip_address}";
	}

	protected function afterInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		if (cache()->enabled() && USER_CACHE_ENABLED) {
			if (! $this->getLoginSuccessful()) {
				$key = $this->getCacheKeyFromIpAddress($this->getInsertIpAddress());
				cache()->setAPCu($key, time(), LOCKOUT_DURATION);
			} elseif ($print) {
				Debug::print("{$f} login was successful");
			}
		} elseif ($print) {
			Debug::error("{$f} cache is disabled");
		}
		return parent::afterInsertHook($mysqli);
	}

	/**
	 * handles everything associated with failing a login
	 *
	 * @param mysqli $mysqli
	 * @param int $status
	 *        	: Status code of login failure reason
	 * @return LoginAttempt
	 */
	public function failInsert(mysqli $mysqli, int $status): LoginAttempt{
		$f = __METHOD__;
		try {
			$print = false;
			if ($status === FAILURE) {
				Debug::error("{$f} impermissably vague failure status");
			}
			// mark login failed
			$this->fail($mysqli, $status);
			// insert into database
			$status = $this->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting failed login attempt returned error status \"{$err}\"");
				// $this->setObjectStatus($status);
			}
			if ($this->getLoginResult() === ERROR_XSRF) {
				Debug::print("{$f} session expired");
				return $this;
			}
			// send failed login notification, unless user has them disabled
			if ($this->hasUserData()) {
				$user = $this->getUserData();
				if ($user->getPushSecurityNotifications() || $user->getEmailSecurityNotifications()) {
					if ($print) {
						Debug::print("{$f} user has security notifications enabled in some way");
					}
					$status = $this->reload($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} reload returned error status \"{$err}\"");
						$this->setObjectStatus($status);
						return $this;
					}
					$user->setTemporaryRole(USER_ROLE_RECIPIENT);
					$status = $user->notify($mysqli, $this);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} sending notification returned error status \"{$err}\"");
						// $this->setObjectStatus($status);
					} elseif ($print) {
						Debug::print("{$f} notification sent successfully");
					}
				} elseif ($print) {
					Debug::print("{$f} user has security notifications disabled");
				}
			} elseif ($print) {
				Debug::print("{$f} there is no user data, someone is trying to login as a nonexistent user");
			}
			// if user has exceeded failed login count, force them to fill out a captcha
			if (Request::isXHREvent() && hasInputParameter('login')) {
				$validator = new LenienthCaptchaValidator(LoginAttempt::class); // , 1);
				if ($validator->validateFailedRequestCount($mysqli) !== SUCCESS) {
					if ($print) {
						Debug::print("{$f} failed request count exceeds maximum; inserting an hCaptcha");
					}
					$response = app()->getResponse(app()->getUseCase());
					$hcaptcha = new hCaptcha();
					$hcaptcha->setIdAttribute("login_hcaptcha");
					$hcaptcha->setCatchReportedSubcommandsFlag(true);
					$command = new InsertBeforeCommand("load_login_form", $hcaptcha);
					$command->setOnDuplicateIdCommand(new NoOpCommand());
					$response->pushCommand($command);
				}
			} elseif ($print) {
				Debug::print("{$f} this is not an XHR evemt, or the user is not logging in");
			}
			return $this;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getEmailNotificationClass():string{
		return FailedLoginEmail::class;
	}

	public function isSecurityNotificationWarranted():bool{
		return ! $this->wasLoginSuccessful();
	}

	public static function getConfirmationCodeClass(): string{
		return null;
	}

	public function getReasonLoggedString():string{
		$f = __METHOD__;
		$print = false;
		if ($this->isDeleted()) {
			if ($print) {
				Debug::print("{$f} this login attempt was deleted");
			}
			return _("Login attempt deleted");
		} elseif ($print) {
			Debug::print("{$f} this login attempt was NOT deleted");
		}
		if ($this->wasLoginSuccessful()) {
			return _("Successful login");
		}
		return _("Failed login");
	}

	public function getReasonLogged(){
		return $this->getLoginResult();
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try {
			$print = false;
			$successful = $this->wasLoginSuccessful();
			if (! $successful) {
				Debug::warning("{$f} login failed; I am very disappointed in you, user");
			}
			$status = parent::afterGenerateInitialValuesHook();
			$post = getInputParameters();
			if (isset($post['name'])) {
				$name = $post['name'];
			} else {
				$session = new FullAuthenticationData();
				if ($session->hasUsername()) {
					$name = $session->getUsername();
				} else {
					$result = $this->getLoginResult();
					if ($result === RESULT_BFP_MFA_FAILED) {
						$name = $this->getUserData()->getNormalizedName();
					} else {
						$err = ErrorMessage::getResultMessage($result);
						Debug::error("{$f} name was not posted; this object has error status {$result} \"{$err}\"");
					}
				}
			}
			$this->setUserName(NameDatum::normalize($name));
			if (! $this->hasUserAccountType()) {
				$this->setUserAccountType(ACCOUNT_TYPE_UNDEFINED);
			}
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getSuccessfulResultCode():int{
		return SUCCESS;
	}

	public function preventDuplicateEntry(mysqli $mysqli): int{
		return SUCCESS;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$userName = new TextDatum("userName");
		static::pushTemporaryColumnsStatic($columns, $userName);
	}

	protected function sendLockoutEmail(mysqli $mysqli){
		$f = __METHOD__;
		try {
			$print = false;
			$user = $this->getUserData();
			if ($user === null) {
				Debug::error("{$f} user returned null");
			} elseif ($print) {
				Debug::print("{$f} about to submit lockout confirmation code");
			}
			return LockoutConfirmationCode::submitStatic($mysqli, $user);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function checkUserMFAEnabled(mysqli $mysqli):int{
		$f = __METHOD__;
		$print = false;
		$user = $this->getUserData();
		if ($user == null) {
			Debug::warning("{$f} user data returned null");
			return $this->setLoginResult(ERROR_NULL_USER_OBJECT);
		} elseif ($user instanceof AnonymousUser) {
			Debug::warning("{$f} user is anonymous");
			return $this->setObjectStatus(ERROR_INTERNAL);
		} elseif ($user->getMFAStatus() == MFA_STATUS_ENABLED) {
			if (! $user->hasMfaSeed()) {
				Debug::warning("{$f} MFA is enabled, but seed is undefined");
			}
			return $this->setLoginResult(RESULT_BFP_MFA_CONFIRM);
		} elseif ($print) {
			Debug::print("{$f} login successful; user does not have multifactor authentication enabled");
		}
		return $this->setLoginResult(SUCCESS);
	}

	public function bruteforceProtection(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			$select = $this->select("insertTimestamp")
				->where(new AndCommand(new WhereCondition('userName', OPERATOR_EQUALS), new WhereCondition('loginSuccessful', OPERATOR_EQUALS), new WhereCondition('insertTimestamp', OPERATOR_GREATERTHAN)))
				->orderBy(new OrderByClause("insertTimestamp", DIRECTION_DESCENDING))
				->limit(MAX_FAILED_LOGINS_BY_NAME + 2)
				->withTypeSpecifier('sii')
				->withParameters([
				$this->getUserName(),
				FAILURE,
				time() - LOCKOUT_DURATION
			]);
			if ($print) {
				Debug::print("{$f} failed login select statement: \"{$select}\"");
			}
			$count = $select->executeGetResultCount($mysqli);
			if ($print) {
				Debug::print("{$f} failed login count for username is {$count}");
			}
			if ($count > MAX_FAILED_LOGINS_BY_NAME) {
				if ($count === MAX_FAILED_LOGINS_BY_NAME + 1) {
					$this->sendLockoutEmail($mysqli);
				}
				return $this->setLoginResult(RESULT_BFP_USERNAME_LOCKOUT_START);
			} elseif ($print) {
				Debug::print("{$f} returning parent function");
			}
			return parent::bruteforceProtection($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function fail(mysqli $mysqli, int $result):int{
		$f = __METHOD__;
		$print = false;
		$count = $this->getFailedLoginCountForIpAddress($mysqli);
		if ($print) {
			Debug::print("{$f} failed login count for IP address: {$count}");
		}
		if ($count > MAX_FAILED_LOGINS_BY_IP) {
			return $this->setLoginResult(RESULT_BFP_IP_LOCKOUT_START);
		}
		$this->setLoginResult($result);
		return RESULT_LOGIN_FAILURE_CONTINUE;
	}

	protected function getFailedLoginCountForIpAddress(mysqli $mysqli):int{
		$f = __METHOD__;
		try {
			return $this->select("num", "insertTimestamp", "loginSuccessful", "loginResult", "insertIpAddress")
				->where(new AndCommand(new WhereCondition("insertTimestamp", OPERATOR_GREATERTHAN), new WhereCondition("insertIpAddress", OPERATOR_EQUALS), new WhereCondition("loginSuccessful", OPERATOR_EQUALS)))
				->orderBy(new OrderByClause("insertTimestamp", DIRECTION_DESCENDING))
				->limit(7)
				->withTypeSpecifier('isi')
				->withParameters([
				$this->generateExpiredTimestamp(),
				$_SERVER['REMOTE_ADDR'],
				static::getCautionResult()
			])->executeGetResultCount($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected static function getCautionResult():int{
		return FAILURE;
	}

	public function getUserKey():string{
		if (! $this->hasUserKey()) {
			$str = $this->getUserName();
			$key = UserData::getKeyFromName($str);
			return $this->setUserKey($key);
		}
		return $this->getColumnValue('userKey');
	}

	protected static function isFailedLoginResult(int $login_result):bool{
		if (false !== array_search($login_result, [
			SUCCESS,
			RESULT_BFP_MFA_CONFIRM,
			RESULT_BFP_WAIVER_SUCCESS
		])) {
			return true;
		} else {
			return false;
		}
	}

	public function isPushNotificationWarranted():bool{
		if ($this->wasLoginSuccessful()) {
			return false;
		}
		return true;
	}

	public static function getPrettyClassName(?string $lang = null):string{
		return _("Login attempt");
	}

	public static function getPrettyClassNames(?string $lang = null):string{
		return _("Login attempts");
	}

	public static function getTableNameStatic(): string
	{
		return "login_attempts";
	}

	public static function getIpLogReason(){
		return BECAUSE_LOGIN;
	}

	public static function getAccessTypeStatic(): string{
		return ACCESS_TYPE_LOGIN;
	}
}
