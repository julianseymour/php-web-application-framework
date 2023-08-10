<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\AbstractLoginUseCase;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticateUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadTreeUseCase;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

abstract class MfaUseCase extends AbstractLoginUseCase
{

	public function loadUserData(mysqli $mysqli, LoginAttempt $attempt): ?AuthenticatedUser
	{
		$f = __METHOD__;
		try {
			$print = false;
			$directive = directive();
			if ($directive !== DIRECTIVE_MFA) {
				Debug::printPost("{$f} wrong directive \"{$directive}\"");
			} elseif ($print) {
				Debug::print("{$f} user is submitting a login MFA OTP");
			}
			$session = new PreMultifactorAuthenticationData();
			$user_class = $this->getAuthenticatedUserClass();
			$user = new $user_class(ALLOCATION_MODE_SUBJECTIVE);
			if (! $session->hasUserKey()) {
				if ($print) {
					Debug::print("{$f} serialized row is undefined");
				}
				$attempt->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
				return null;
			}
			$user_key = $session->getUserKey();
			$cached = false;
			if (cache()->enabled() && USER_CACHE_ENABLED) {
				if (cache()->hasAPCu($user_key)) {
					$cached = true;
					$user->setFlag("cached", true);
				} else {
					Debug::error("{$f} user data was not cached");
				}
			} elseif ($print) {
				Debug::print("{$f} cache is disabled");
			}
			if (! $cached) {
				$result = $user->select()
					->where(new WhereCondition($user->getIdentifierName(), OPERATOR_EQUALS))
					->withTypeSpecifier('s')
					->withParameters($user_key)
					->executeGetResult($mysqli);
				$results = $result->fetch_all(MYSQLI_ASSOC);
				$result->free_result();
				$count = count($results);
				if ($count === 0) {
					if ($print) {
						Debug::error("{$f} no results");
					}
					$attempt->setObjectStatus(ERROR_LOGIN_CREDENTIALS);
					return null;
				} elseif ($count > 1) {
					Debug::error("{$f} {$count} results");
				}
				$results = $results[0];
			} elseif ($print) {
				Debug::print("{$f} results were cached");
			}
			$status = $user->processQueryResultArray($mysqli, $results);
			switch ($status) {
				case SUCCESS:
					if ($print) {
						Debug::print("{$f} successfully loaded user data; about to load foreign data structures");
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
					/*
					 * if(cache()->enabled() && USER_CACHE_ENABLED){
					 * $columns = $user->getFilteredColumns(COLUMN_FILTER_DIRTY_CACHE);
					 * if(!empty($columns)){
					 * foreach($columns as $column_name => $column){
					 * $results[$column_name] = $column->getDatabaseEncodedValue();
					 * $column->setDirtyCacheFlag(false);
					 * }
					 * }elseif($print){
					 * Debug::print("{$f} there are no dirty cache flagged columns");
					 * }
					 * }elseif($print){
					 * Debug::print("{$f} redis cache flag is not enabled; skipping cleanup of dirty cacheable foreign keys");
					 * }
					 * if(cache()->enabled() && USER_CACHE_ENABLED){
					 * $user->setCacheValue($results);
					 * }elseif($print){
					 * Debug::print("{$f} redis cache is disabled; skippins setCacheValue");
					 * }
					 */
					$status = $user->loadForeignDataStructures($mysqli, false);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loadForeignDataStructures returned error status \"{$err}\"");
						$attempt->setObjectStatus($status);
						return null;
					}
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} client has error status \"{$err}\"");
					$attempt->setObjectStatus($status);
					return null;
			}
			$key = $user->getIdentifierValue();
			if (! registry()->hasObjectRegisteredToKey($key)) {
				registry()->registerObjectToKey($key, $user);
			}
			return $attempt->setUserData($user);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function validateLoginMfaOtp($mysqli)
	{
		$f = __METHOD__;
		try {
			$print = false;
			// verify half-login flag is set
			$session = new PreMultifactorAuthenticationData();
			if (! $session->hasSignature()) {
				if ($print) {
					Debug::print("{$f} user is not half logged in");
				}
				Debug::printArray($_SESSION);
				Debug::warning("{$f} user is not half-logged in");
				// app()->setUserData($guest);
				return $this->setObjectStatus(RESULT_BFP_RETRY_LOGIN);
			} elseif ($print) {
				Debug::print("{$f} half login flag is defined");
			}
			// get user data
			if ($print) {
				Debug::print("{$f} about to call getAllegedCurrentUser");
			}
			$user = AuthenticateUseCase::getAllegedCurrentUser($mysqli, LOGIN_TYPE_PARTIAL);
			if ($user === null) {
				Debug::error("{$f} getAllegedCurrentUser returned null");
			}
			// verify user had MFA enabled
			if (! $user->getMFAStatus()) {
				Debug::warning("{$f} MFA is disabled, you shouldn't be here");
				return $this->setObjectStatus(RESULT_MFA_DISABLED);
			} elseif (! $user->hasMfaSeed()) {
				Debug::warning("{$f} MFA seed is undefined");
				return $this->setObjectStatus(RESULT_MFA_DISABLED);
			} elseif ($print) {
				Debug::print("{$f} MFA OTP value is defined");
			}
			// create login attempt
			$attempt = new LoginAttempt();
			$attempt->setUserData($user);
			if (! $attempt->hasUserKey()) {
				Debug::error("{$f} after setting user data, login attempt does not have a user key");
			} elseif ($print) {
				$user_key = $attempt->getUserKey();
				Debug::print("{$f} after setting user data, login attempt user key is defined as \"{$user_key}\"");
			}
			$attempt->getColumn("userKey")->seal();
			$status = $this->initializeAccessAttempt($mysqli, $attempt);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} initializeAccessAttempt returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} initializeAccessAttempt was successful");
			}
			// validate latent authentication data
			$status = $user->authenticate($mysqli, LOGIN_TYPE_PARTIAL);
			if ($status !== SUCCESS) {
				if ($print) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} reauthenticate returned error status \"{$err}\"");
				}
				$this->setObjectStatus(RESULT_BFP_MFA_FAILED);
				$attempt->setLoginResult($status);
				// return $this->setObjectStatus($status);
			} else {
				if ($print) {
					Debug::print("{$f} validated partial login -- about to validate MFA OTP");
				}
				// get user MFA seed
				$seed = $user->getMfaSeed();
				if (empty($seed)) {
					Debug::error("{$f} mfa seed returned null");
					return $this->setObjectStatus(ERROR_NULL_MFA_SEED);
				} elseif (! $user->hasUserNameData()) {
					Debug::error("{$f} user lacks username data");
				} elseif ($print) {
					Debug::print("{$f} user has both MFA seed and username data");
				}
				// validate user MFA seed
				$validator = new MfaOtpValidator('otp');
				$validator->setUserData($user);
				$post = getInputParameters();
				$mfade = $validator->validate($post);
				// transition user to fully logged in or failed
				if ($mfade === SUCCESS) {
					if ($print) {
						Debug::print("{$f} MFA OTP validation successful");
					}
					if ($user instanceof AnonymousUser) {
						Debug::error("{$f} user is anonymous");
					} elseif ($print) {
						$username = $user->getName();
						Debug::print("{$f} username is \"{$username}\"");
					}
					// app()->setUserData($user);
					$attempt->setLoginResult(SUCCESS);
					$sdc = new FullAuthenticationData();
					$sdc->setPreviousLoginMode(LOGIN_TYPE_PARTIAL);
					$sdc->setFullLoginFlag(true);
					$sdc->ejectDeterministicSecretKey();
					$sdc->handSessionToUser($user);
				} else {
					if ($print) {
						$err = ErrorMessage::getResultMessage($mfade);
						Debug::print("{$f} MFA OTP failed with error status \"{$err}\"");
					}
					$this->setObjectStatus(RESULT_BFP_MFA_FAILED);
					$attempt->setLoginResult($mfade);
				}
			}
			// insert login attempt
			if (! $attempt->hasUserKey()) {
				Debug::error("{$f} before insertion, login attempt does not have a user key");
			} elseif ($print) {
				$new_user_key = $attempt->getUserKey();
				Debug::print("{$f} before insertion, login attempt user key is defined as \"{$new_user_key}\"");
				if ($user_key !== $new_user_key) {
					Debug::error("{$f} new user key \"{$new_user_key}\" differs from old one \"{$user_key}\"");
				}
			}
			$status = $attempt->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				static::debugErrorStatic("{$f} \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// unset session variables if necessary
			$result = $attempt->getLoginResult();
			if ($result !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($result);
				Debug::warning("{$f} completing half login returned error status \"{$err}\"");
				$session->unsetColumnValues();
				// $user->unsetSessionVariables(LOGIN_TYPE_PARTIAL);
				$user = AuthenticateUseCase::getAnonymousUser();
			} elseif ($print) {
				Debug::print("{$f} MFA OTP validation & login attempt insertion successful");
			}
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return $this->setObjectStatus($result);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function execute(): int
	{
		$f = __METHOD__;
		try {
			Debug::print("{$f} entered");
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if ($mysqli->connect_errno) {
				Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
			} elseif (! $mysqli->ping()) {
				Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
			}

			$guest = AuthenticateUseCase::getAnonymousUser();
			app()->setUserData($guest);
			$status = $this->validateLoginMfaOtp($mysqli);
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} validateLoginMfaOtp returned error status \"{$err}\"");
			/*$auth = new AuthenticateUseCase($this);
			$auth->validateTransition();
			$auth->execute();*/
			$load = new LoadTreeUseCase($this);
			$load->validateTransition();
			$load->execute();
			Debug::print("{$f} returning normally");
			return $this->setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getActionAttribute(): ?string
	{
		return "/login_mfa";
	}

	public function getUseCaseId()
	{
		return USE_CASE_LOGIN_MFA;
	}

	public function getAuthenticatedUserClass(): string
	{
		return config()->getNormalUserClass();
	}

	public function getClientUseCaseName(): ?string
	{
		return "mfa";
	}
}
