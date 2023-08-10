<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\lockout\LockoutWaiverAttempt;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticateUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadTreeUseCase;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\LenienthCaptchaValidator;
use JulianSeymour\PHPWebApplicationFramework\security\honeypot\HoneypotValidator;
use JulianSeymour\PHPWebApplicationFramework\session\hijack\AntiHijackSessionData;
use JulianSeymour\PHPWebApplicationFramework\validate\FormButtonValidator;
use Exception;
use mysqli;

abstract class UnresponsiveLoginUseCase extends AbstractLoginUseCase
{

	protected $loginAttempt;

	public function setLoginAttempt($attempt)
	{
		return $this->loginAttempt = $attempt;
	}

	public function hasLoginAttempt()
	{
		return isset($this->loginAttempt) && $this->loginAttempt instanceof LoginAttempt;
	}

	public function getLoginAttempt()
	{
		$f = __METHOD__;
		if (! $this->hasLoginAttempt()) {
			Debug::error("{$f} login attempt is undefined");
		}
		return $this->loginAttempt;
	}

	public function login(mysqli $mysqli): ?LoginAttempt
	{
		$f = __METHOD__; //UnresponsiveLoginUseCase::getShortClass()."(".static::getShortClass().")->login()";
		try {
			$print = false;
			// initialize login attempt object
			$attempt = new LoginAttempt();
			$this->setLoginAttempt($attempt);
			$attempt->setAuthenticatedUserClass($this->getAuthenticatedUserClass());
			$status = $this->initializeAccessAttempt($mysqli, $attempt);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} initializing login attempt returned error status \"{$err}\"");
				$attempt->failInsert($mysqli, $status);
				return $attempt;
			} elseif ($print) {
				Debug::print("{$f} record of login attempt initialized successfully");
			}
			// validate
			$login_form_class = $this->getLoginFormClass();
			$validator = new FormButtonValidator(new $login_form_class(ALLOCATION_MODE_LAZY));
			$honeypots = new HoneypotValidator($login_form_class);
			$captcha = new LenienthCaptchaValidator(LoginAttempt::class, 1);
			$validator->pushCovalidators($honeypots, $captcha);
			$params = getInputParameters();
			$valid = $validator->validate($params);
			if ($valid !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($valid);
				Debug::warning("{$f} LoginValidator->validate() returned error status {$err}");
				$attempt->failInsert($mysqli, $valid);
				return $attempt;
			} elseif ($print) {
				Debug::print("{$f} login form validated successfully");
			}
			// validate current IP address for user's account firewall
			$user = $attempt->getUserData();
			iF($print){
				$did = $user->getDebugId();
				$decl = $user->getDeclarationLine();
				Debug::print("{$f} user has debug ID {$did} and was instantiated {$decl}");
			}
			$user->setRequestEventObject($attempt);
			$status = $user->validateCurrentIpAddress($mysqli); //, $_SERVER['REMOTE_ADDR']);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} filterIpAddress returned error status \"{$err}\"");
				$attempt->failInsert($mysqli, $status);
				return $attempt;
			} elseif ($print) {
				Debug::print("{$f} current IP address validated successfully");
			}
			// brute force protection
			$status = $attempt->bruteForceProtection($mysqli);
			if ($status !== SUCCESS) { // failed bruteforce protection, checking for lockout waiver
				if($print){
					Debug::print("{$f} failed bruteforce protection, checking lockout waiver");
				}
				$select = LockoutWaiverAttempt::selectStatic(
					null, "uniqueKey", "confirmationCodeType", "loginSuccessful", "insertTimestamp"
				)->where(
					new AndCommand(
						new WhereCondition("confirmationCodeType", OPERATOR_EQUALS),
						LockoutWaiverAttempt::whereIntersectionalHostKey(
							$user->getClass(), 
							"userKey"
						), 
						new WhereCondition("loginSuccessful", OPERATOR_EQUALS), 
						new WhereCondition("insertTimestamp", OPERATOR_GREATERTHANEQUALS)
					)
				)->withTypeSpecifier('sssii')->withParameters(
					ACCESS_TYPE_LOCKOUT_WAIVER, 
					$user->getIdentifierValue(), 
					"userKey", 
					SUCCESS, 
					time() - LOCKOUT_DURATION
				)->orderBy(new OrderByClause("insertTimestamp", DIRECTION_DESCENDING));
				if ($print) {
					Debug::print("{$f} lockout waiver select statement: \"{$select}\"");
				}
				$count = $select->executeGetResultCount($mysqli);
				if ($count === 0) {
					if ($print) {
						Debug::print("{$f} no lockout waiver found");
					}
					$attempt->failInsert($mysqli, $status);
					return $attempt;
				} elseif ($print) {
					Debug::print("{$f} lockout waiver is in effect");
				}
			} elseif ($print) {
				Debug::print("{$f} bruteforce protection passed successfully");
			}
			// validate password hash
			if (! hasInputParameter("password")) {
				Debug::warning("{$f} posted password is null");
				$status = ERROR_PASSWORD_UNDEFINED;
				// return $this->setLoginResult($this->setObjectStatus());
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} error: \"{$err}\"");
				$attempt->failInsert($mysqli, $status);
				return $attempt;
			} elseif (! password_verify(getInputParameter('password'), $user->getPasswordHash())) {
				if ($print) {
					Debug::print("{$f} password verification failed");
				}
				$status = ERROR_LOGIN_CREDENTIALS;
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} password verification failed: \"{$err}\"");
				$attempt->failInsert($mysqli, $status);
				return $attempt;
			} elseif ($print) {
				Debug::print("{$f} password verified successfully");
			}
			$user->setTemporaryRole(USER_ROLE_RECIPIENT);
			// instantiate anti-hijack session data
			if ($user->getBindIpAddress() || $user->getBindUserAgent()) {
				$hijack = new AntiHijackSessionData();
				$hijack->setUserData($user);
			} elseif ($print) {
				Debug::print("{$f} the user does not bind IP address or user agent");
			}
			// check if user has MFA enabled and initialize authentication data for user
			if ($user->getMFAStatus() == MFA_STATUS_ENABLED) {
				if (! $user->hasMfaSeed()) {
					Debug::error("{$f} MFA is enabled, but seed is undefined");
				} elseif ($print) {
					Debug::print("{$f} the user has multifactor authentication enabled; about to half-log the user in");
				}
				$sd = new PreMultifactorAuthenticationData();
				if ($sd->hasDeterministicSecretKey()) {
					$sd->ejectDeterministicSecretKey();
				}
				$sd->handSessionToUser($user, LOGIN_TYPE_UNDEFINED);
				$attempt->setLoginResult(RESULT_BFP_MFA_CONFIRM);
			} else {
				if ($print) {
					Debug::print("{$f} user logged in successfully");
				}
				$sdc = $user->getFullAuthenticationDataClass();
				if ($print) {
					Debug::print("{$f} authentication data class is \"{$sdc}\"");
				}
				$sd = new $sdc();
				if ($sd->hasDeterministicSecretKey()) {
					$sd->ejectDeterministicSecretKey();
					if ($sd->hasDeterministicSecretKey()) {
						Debug::error("{$f} immediately after ejection, deterministic secret key is still defined");
					}
				} elseif (array_key_exists("determisticSecretKey", $_SESSION)) {
					Debug::waring("{$f} deterministic secret key is still in session");
					Debug::printArray($_SESSION);
					Debug::printStackTrace();
				} elseif ($print) {
					Debug::print("{$f} ejected deterministic secret key prior to session reinitialization for logged in user");
				}
				$sd->handSessionToUser($user, LOGIN_TYPE_UNDEFINED);
				$attempt->setLoginResult(SUCCESS);
			}
			// insert login attempt
			$status = $attempt->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($this->setObjectStatus($status));
				Debug::warning("{$f} inserting login attempt returned error status \"{$err}\"");
				return $attempt;
			} elseif ($print) {
				$result = $attempt->getLoginResult();
				$err = ErrorMessage::getResultMessage($result);
				Debug::print("{$f} returning normally with result \"{$err}\"");
			}
			return $attempt;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function execute(): int
	{
		$f = __METHOD__; //UnresponsiveLoginUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$print = false;
			PreMultifactorAuthenticationData::unsetColumnValuesStatic();
			if ($print) {
				Debug::print("{$f} IP address is {$_SERVER['REMOTE_ADDR']}. About to call AuthenticateUseCase::getAnonymousUser");
			}
			$user = AuthenticateUseCase::getAnonymousUser();
			app()->setUserData($user);
			if ($print) {
				Debug::print("{$f} assigned anonymous user successfully");
			}
			$mysqli = db()->getConnection(new PublicWriteCredentials());
			if (! isset($mysqli)) {
				$status = $this->setObjectStatus(ERROR_MYSQL_CONNECT);
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} {$err}");
				return $this->setObjectStatus($status);
			}
			$attempt = $this->login($mysqli);
			if ($attempt == null) {
				Debug::error("{$f} login returned null");
			}
			$status = $attempt->getLoginResult();
			if (! $attempt->wasLoginSuccessful()) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} login failed with error status \"{$err}\"");
				if ($status === FAILURE) {
					Debug::error("{$f} could you please be more specific");
				}
				$user->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} login successful");
			}
			// $this->authenticate();
			$auth = new AuthenticateUseCase($this);
			$auth->validateTransition();
			$auth->execute();
			$mysqli = db()->getConnection();
			$load = new LoadTreeUseCase($this);
			$load->validateTransition();
			$load->execute();
			// $this->load($mysqli);
			$result = $attempt->getLoginResult();
			if ($print) {
				$err = ErrorMessage::getResultMessage($result);
				Debug::print("{$f} returning with error status \"{$err}\"");
			}
			if ($this->hasPredecessor()) {
				$this->getPredecessor()->setObjectStatus($result);
			}
			return $this->setObjectStatus($result);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getLoginFormClass(): string
	{
		return LoginForm::class;
	}

	public function getAuthenticatedUserClass(): string
	{
		return config()->getNormalUserClass();
	}

	public function getActionAttribute(): ?string
	{
		return "/login";
	}

	public function getUseCaseId()
	{
		return USE_CASE_LOGIN;
	}

	public function getClientUseCaseName(): ?string
	{
		return "login";
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @return NormalUser
	 */
	public function loadUserData(mysqli $mysqli, LoginAttempt $attempt): ?AuthenticatedUser{
		$f = __METHOD__;
		try {
			$print = false;
			// $post = getInputParameters();
			if (hasInputParameter("name")) {
				$name = getInputParameter("name"); // urldecode($post['name']);
				if ($print) {
					Debug::print("{$f} posted name is \"{$name}\"");
				}
			} else {
				Debug::printPost("{$f} name was not posted");
			}
			$normalized = NameDatum::normalize($name);
			$user_class = $this->getAuthenticatedUserClass();
			$user = new $user_class(ALLOCATION_MODE_SUBJECTIVE);
			$user->setNormalizedName($normalized);
			$select = $user->getNormalizedNameSelectStatement($normalized);
			$result = $select->executeGetResult($mysqli);
			$results = $result->fetch_all(MYSQLI_ASSOC);
			$result->free_result();
			$count = count($results);
			if ($count === 0) {
				if ($print) {
					Debug::warning("{$f} no results");
				}
				$attempt->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
				return null;
			} elseif ($count > 1) {
				Debug::error("{$f} {$count} results");
			}
			$results = $results[0];
			$status = $user->processQueryResultArray($mysqli, $results);
			switch ($status) {
				case SUCCESS:
					if ($print) {
						Debug::print("{$f} successfully loaded user from normalized name; about to load foreign data structures");
					}
					// load foreign keys stored in intersections tables
					$status = $user->loadIntersectionTableKeys($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loadIntersectionTableKeys returned error status \"{$err}\"");
						$attempt->setObjectStatus($status);
						return null;
					}
					// update temporary query results with foreign keys from intersection tables
					if (cache()->enabled() && USER_CACHE_ENABLED) {
						$columns = $user->getFilteredColumns(COLUMN_FILTER_DIRTY_CACHE);
						if (! empty($columns)) {
							foreach ($columns as $column_name => $column) {
								$results[$column_name] = $column->getDatabaseEncodedValue();
								$column->setDirtyCacheFlag(false);
							}
						} elseif ($print) {
							Debug::print("{$f} there are no dirty cache flagged columns");
						}
					} elseif ($print) {
						Debug::print("{$f} redis cache flag is not enabled; skipping cleanup of dirty cacheable foreign keys");
					}
					$user->setCacheValue($results);
					$status = $user->loadForeignDataStructures($mysqli, false);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loadForeignDataStructures returned error status \"{$err}\"");
						$attempt->setObjectStatus($status);
						return null;
					}
					break;
				case ERROR_NOT_FOUND:
					$attempt->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
					return null;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} client has error status \"{$err}\"");
					$attempt->setObjectStatus($status);
					return null;
			}
			$key = $user->getIdentifierValue();
			if($print){
				if (registry()->hasObjectRegisteredToKey($key)) {
					$obj = registry()->get($key);
					$did = $obj->getDebugId();
					$decl = $obj->getDeclarationLine();
					Debug::print("{$f} there was already something registered to key \"{$key}\". It has debug ID {$did} and was instantiated {$decl}");
				}
			}
			registry()->update($key, $user);
			return $attempt->setUserData($user);
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
