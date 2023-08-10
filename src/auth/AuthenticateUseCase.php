<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\Workflow;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\session\resume\GuestSessionRecoveryCookie;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryCookie;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\registry;

/**
 * This is a helper class to move authentication-related functionality into its own UseCase and make it easier to substitute the authentication mechanism.
 * It does not function as a proper UseCase and probably shouldn't be used as one.
 *
 * @author j
 *
 */
class AuthenticateUseCase extends UseCase
{

	/**
	 * Return the user who is supposedly logged in according to session variables, without authenticating
	 *
	 * @return AuthenticatedUser|NULL
	 */
	public static function getAllegedCurrentUser($mysqli, $mode = LOGIN_TYPE_UNDEFINED): ?PlayableUser
	{
		$f = __METHOD__; //AuthenticateUseCase::getShortClass()."(".static::getShortClass().")::getAllegedCurrentUser()";
		try {
			$print = false;
			if (! isset($mysqli)) {
				Debug::error("{$f} mysqli connection failed");
			} elseif ($print) {
				Debug::print("{$f} entered");
			}
			// $half = false;
			switch ($mode) {
				case LOGIN_TYPE_PARTIAL:
					$session = new PreMultifactorAuthenticationData();
					// $half = true;
					break;
				case LOGIN_TYPE_FULL:
					$session = new FullAuthenticationData();
					break;
				default:
					Debug::error("{$f} invalid authentication mode \"{$mode}\"");
			}
			if (! $session->hasUserKey()) {
				Debug::warning("{$f} unique key is undefined in session variables; user mustn't be logged in");
				// Debug::printSession();
				return null;
			} elseif (! $session->hasUserAccountType()) {
				$session->unsetColumnValues();
				Debug::error("{$f} user key should not be defined if user account type is");
				return null;
			} elseif ($print) {
				Debug::print("{$f} session has a user key");
			}
			$class = mods()->getUserClass($session->getUserAccountType());
			if ($print) {
				Debug::print("{$f} client class name is \"{$class}\"");
			}
			$reload = true;
			$user_key = $session->getUserKey();
			if(registry()->hasObjectRegisteredToKey($user_key)){
				if($print){
					Debug::print("{$f} user with key \"{$user_key}\" was already registered, about to check its allocation mode");
				}
				$user = registry()->getRegisteredObjectFromKey($user_key);
				if($user->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
					if($print){
						Debug::print("{$f} user was already registered with the correct allocation mode");
					}
					$reload = false;
				}elseif($print){
					Debug::print("{$f} registered user has the wrong allocation mode, going to reload it");
				}
			}elseif($print){
				Debug::print("{$f} the registry doesn't know about anything with identifier \"{$user_key}\"");
			}
			if($reload){
				if($print){
					Debug::print("{$f} loading user from the database or cache");
				}
				$user = new $class(ALLOCATION_MODE_SUBJECTIVE);
				$cached = false;
				if (cache()->enabled() && USER_CACHE_ENABLED) {
					if($print){
						Debug::print("{$f} cache is enabled");
					}
					if (cache()->hasAPCu($user_key)) {
						if($print){
							Debug::print("{$f} user data is cached");
						}
						$results = cache()->getAPCu($user_key);
						if (is_string($results)) {
							Debug::print("{$f} redis cache results for user key \"{$user_key}\" are \"{$results}\"");
							if ($results[0] !== "{") {
								Debug::error("{$f} cached results have corrupt JSON");
							}
							$results = json_decode($results, true);
						}
						$cached = true;
						$user->setFlag("cached", true);
						if (! is_array($results)) {
							$gottype = is_object($results) ? get_class($results) : gettype($results);
							Debug::warning("{$f} results decoded to \"{$gottype}\"");
							cache()->delete($user_key);
							Debug::printStackTrace();
						}
						if ($print) {
							Debug::print("{$f} account information for user \"{$user_key}\" was cached");
							Debug::disarmTrap();
							// Debug::printArray($results);
						}
					} elseif ($print) {
						Debug::print("{$f} account information for user \"{$user_key}\" was NOT cached");
					}
				} elseif ($print) {
					Debug::print("{$f} user cache is disabled");
				}
				if (! $cached) {
					// load from database
					if ($print) {
						Debug::print("{$f} cache miss for user data with key \"{$user_key}\"");
					}
					$result = $user->select()
						->where(new WhereCondition($user->getIdentifierName(), OPERATOR_EQUALS))
						->withTypeSpecifier('s')
						->withParameters($user_key)
						->executeGetResult($mysqli);
					$count = $result->num_rows;
					if ($count !== 1) {
						$uc = $user->getClass();
						if ($print) {
							Debug::warning("{$f} user of class {$uc} with key \"{$user_key}\" was not found");
						}
						$session->unsetColumnValues();
						return null;
					}
					$results = $result->fetch_all(MYSQLI_ASSOC);
					$result->free_result();
					$results = $results[0];
				} elseif ($print) {
					Debug::print("{$f} cache hit for user key \"{$user_key}\"");
				}
				$user->setReceptivity(DATA_MODE_PASSIVE);
				$status = $user->processQueryResultArray($mysqli, $results);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
					$user->setObjectStatus($status);
					return null;
				} elseif ($print) {
					Debug::print("{$f} processQueryResultArray was successful");
				}
				registry()->update($user->getIdentifierValue(), $user);
				if (! $cached) {
					// load foreign keys stored in intersection tables
					$status = $user->loadIntersectionTableKeys($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loadIntersectionTableKeys returned error status \"{$err}\"");
						$user->setObjectStatus($status);
						return null;
					} elseif ($print) {
						Debug::print("{$f} loadIntersectionTableKeys was successful");
					}
					// update temporary query results to contain values loaded from intersection tables
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
						$user->setCacheValue($results);
					} elseif ($print) {
						Debug::print("{$f} user cache is disabled");
					}
				}
			}else{
				if($print){
					Debug::print("{$f} user was already registered with the correct allocation mode");
				}
				$status = SUCCESS;
			}
			$user->setObjectStatus($status);
			$status = $user->loadForeignDataStructures($mysqli, false, 3);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loading foreign data structures returned error status \"{$err}\"");
				return null;
			}
			$session->setUserData($user);
			return $user;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getTransitionFromPermission()
	{
		return SUCCESS;
	}

	public function getExecutePermission()
	{
		return SUCCESS;
	}

	/**
	 *
	 * @param UseCase $use_case
	 * @return AnonymousUser|NULL
	 */
	public static function getAnonymousUser(): ?AnonymousUser
	{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$user = null;
			$guest_class = config()->getGuestUserClass();
			$session = new FullAuthenticationData();
			$has_session = $session->hasUserKey();
			if ($has_session) { // if session has a user key, get that user
				$key = $session->getUserKey();
				if ($print) {
					Debug::print("{$f} guest user key \"{$key}\" is set in session memory");
				}
				$user = new $guest_class(ALLOCATION_MODE_SUBJECTIVE);
				$user->setReceptivity(DATA_MODE_PASSIVE);
				$mysqli = db()->getConnection(PublicReadCredentials::class);
				$cached = false;
				if (cache()->enabled() && USER_CACHE_ENABLED) {
					if (cache()->hasAPCu($key)) {
						$cached = true;
						$user->setFlag("cached", true);
					}
				} elseif ($print) {
					Debug::print("{$f} user cache is disabled, skipping cache check for guest user with key \"{$key}\"");
				}
				if ($cached) {
					$results = cache()->getAPCu($key);
					$user->processQueryResultArray($mysqli, $results);
				} else {
					if ($mysqli == null) {
						Debug::warning("{$f} mysqli connection returned null");
						$user->stubbify();
						$user->setObjectStatus(SUCCESS);
					} elseif (false && ! QueryBuilder::tableExists($mysqli, $guest_class::getDatabaseNameStatic(), $guest_class::getTableNameStatic())) {
						Debug::warning("{$f} guest user table has not been created yet");
						$user->stubbify();
						$user->setObjectStatus(SUCCESS);
						// return $user;
					} else {
						if ($print) {
							Debug::print("{$f} about to load user with key \"{$key}\"");
						}
						$result = $user->select()
							->where(new WhereCondition($user->getIdentifierName(), OPERATOR_EQUALS))
							->withTypeSpecifier('s')
							->withParameters($key)
							->executeGetResult($mysqli);
						$count = $result->num_rows;
						if ($count > 1) {
							Debug::error("{$f} multiple entries for user with key \"{$key}\"");
						} elseif ($count === 0) { //if the user does not exit it's probably because this user has been spamming new accounts
							if ($print) {
								Debug::print("{$f} user does not exist; let's try again");
							}
							$user->stubbify();
							$user->setObjectStatus(SUCCESS);
							$nuc = config()->getNormalUserClass();
							if ($nuc::tableExistsStatic($mysqli)) {
								$idn = $nuc::getIdentifierNameStatic();
								$ts = $nuc::getTypeSpecifierStatic($idn);
								$select = $nuc::selectStatic()->where($idn);
								$mysqli = db()->getConnection(PublicReadCredentials::class);
								$count = $select->prepareBindExecuteGetResultCount($mysqli, $ts, $key);
								if ($count !== 0) {
									$session->ejectUserKey();
									Debug::error("{$f} looks like you still have a registered user key in sesson memory");
								}
							} elseif ($print) {
								Debug::print("{$f} normal user table does not exist yet");
							}
						} elseif ($count === 1) {
							$results = $result->fetch_all(MYSQLI_ASSOC);
							$result->free_result();
							$results = $results[0];
							$status = $user->processQueryResultArray($mysqli, $results);
							if ($status !== SUCCESS) {
								$err = ErrorMessage::getResultMessage($status);
								Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
								$user->setObjectStatus($status);
								return null;
							}
							$status = $user->loadIntersectionTableKeys($mysqli);
							if ($status !== SUCCESS) {
								$err = ErrorMessage::getResultMessage($status);
								Debug::warning("{$f} loadIntersectionTableKeys returned error status \"{$err}\"");
								$user->setObjectStatus($status);
								return null;
							}
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
								$ttl = $user->setTimeToLive(SESSION_TIMEOUT_SECONDS);
								cache()->setAPCu($key, $results, $ttl);
							} elseif ($print) {
								Debug::print("{$f} redis cache is disabled, skipping loaded user cache");
							}
						} elseif ($print) {
							Debug::print("{$f} successfully loaded anonymous user data with key \"{$key}\"");
						}
					}
				}
				// otherwise, set the session authentication data unless the user is logging in
				$directive = directive();
				switch ($directive) {
					case DIRECTIVE_ADMIN_LOGIN:
					case DIRECTIVE_ADMIN_MFA:
					case DIRECTIVE_FORGOT_CREDENTIALS:
					case DIRECTIVE_LOGIN:
					case DIRECTIVE_MFA:
						if ($print) {
							Debug::print("{$f} user is logging in or submitting an MFA OTP -- we'll have to set session variables later");
						}
						break;
					default:
						if ($print) {
							Debug::print("{$f} user is not submitting MFA OTP -- about to set session variables");
						}
						if (! $user->hasIdentifierValue()) {
							if ($print) {
								Debug::print("{$f} key does not exist -- about to generate a new one");
							}
							$user->setColumnValue($user->getIdentifierName(), null);
							$user->generateKey();
							$user->setNotificationDeliveryTimestamp($user->generateInsertTimestamp());
						} elseif ($print) {
							Debug::print("{$f} key was already generated");
						}
						if ($session->hasDeterministicSecretKey()) {
							$session->ejectDeterministicSecretKey();
						}
						$session->handSessionToUser($user, LOGIN_TYPE_UNDEFINED);
						$status = $user->getObjectStatus();
						if ($status !== SUCCESS) {
							$err1 = ErrorMessage::getResultMessage($status);
							$err2 = "{$f} error setting anonymous session variables: \"{$err1}\"";
							Debug::error($err2);
							return $user;
						}
				}
			} else { // if the session does not contain a user key, attempt to reover one from guest cookie
				if ($print) {
					Debug::print("{$f} session does not contain reauthentication data");
				}
				$recovery_cookie = new GuestSessionRecoveryCookie();
				if ($recovery_cookie->hasRecoveryKey()) {
					if ($print) {
						Debug::print("{$f} guest session recovery cookie has a key");
					}
					$user = $recovery_cookie->recoverSession();
				} elseif ($print) {
					Debug::print("{$f} guest session recovery cookie does not have a recovery key");
				}
				// if the user could not be recovered it could be due to garbage information; in this case just make a new one
				// Do not make this an else of the above conditional
				if ($user === null) {
					if ($print) {
						Debug::print("{$f} failed to recover guest user session");
					}
					if (isset($_SESSION) && is_array($_SESSION) && array_key_exists('recursive', $_SESSION)) {
						unset($_SESSION['recursive']);
					}
					if ($print) {
						Debug::print("{$f} about to initialize anonymous session");
					}
					$user = new $guest_class(ALLOCATION_MODE_SUBJECTIVE);
					$status = $user->initializeAnonymousSession();
					if ($status !== SUCCESS) {
						$err1 = ErrorMessage::getResultMessage($status);
						$err2 = "{$f} error initializing anonymous session: \"{$err1}\"";
						return Debug::error($err2);
					}
					$mysqli = db()->getConnection(PublicReadCredentials::class);
					// $status = $user->getObjectStatus(); //reload($mysqli, true);
					switch ($status) {
						case ERROR_NOT_FOUND:
							$user->setObjectStatus(SUCCESS);
							$user->setSerialNumber(0);
						case SUCCESS:
							break;
						default:
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} reloading user returned error status \"{$err}\"");
							return $user;
					}
				} elseif ($print) {
					Debug::print("{$f} successfully recovered guest user session");
				}
			}
			if ($print) {
				Debug::print("{$f} returning successfully");
			}
			$user->setObjectStatus(SUCCESS);
			$session->setUserData($user);
			// app()->setUserData($user);
			return $user;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function anonymize()
	{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} user is not fully logged in -- creating anonymous user data");
		}
		$session = new FullAuthenticationData();
		if (app()->hasUserData() && app()->getUserData() instanceof AnonymousUser) {
			if ($print) {
				Debug::print("{$f} there is already an anoymous user defined by application runtime");
			}
			$user = user();
		} else {
			if ($print) {
				Debug::print("{$f} no anonymous user defined by application runtime; about to get one");
			}
			if ($session->hasDeterministicSecretKey()) {
				$session->ejectDeterministicSecretKey();
			}
			$user = static::getAnonymousUser($this);
			app()->setUserData($user);
		}
		$session->handSessionToUser($user, LOGIN_TYPE_UNDEFINED);
		$user->setObjectStatus(SUCCESS);
		return $user;
	}

	/**
	 *
	 * @return int
	 */
	public function execute(): int
	{
		$f = __METHOD__; //AuthenticateUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$print = false;
			$session = new FullAuthenticationData();
			if (! $session->getFullLoginFlag()) {
				if ($print) {
					Debug::print("{$f} full login flag is not set");
				}
				$recovery_cookie = new SessionRecoveryCookie();
				if ($recovery_cookie->hasRecoveryKey()) {
					$recovery_cookie->recoverSession();
				} else {
					if ($print) {
						Debug::print("{$f} cookie lacks session recovery key");
						// Debug::printArray($_COOKIE);
					}
				}
			} elseif ($print) {
				Debug::print("{$f} full login flag is set");
			}
			if ($print) {
				Debug::print("{$f} about to verify user login state...");
			}
			$mysqli = db()->getConnection(); // leave parameter blank
			if ($mysqli == null) {
				Debug::error("{$f} mysqli object returned null");
			}
			$user = $this->getAllegedCurrentUser($mysqli, LOGIN_TYPE_FULL);
			if ($print) {
				Debug::print("{$f} verified user login state");
			}
			if (! isset($user)) {
				if ($print) {
					Debug::print("{$f} user data returned null");
				}
				$user = $this->anonymize();
			}
			$ucn = $user->getClass();
			if ($print) {
				Debug::print("{$f} user class name is \"{$ucn}\"");
			}
			app()->setUserData($user);
			if ($print) {
				Debug::print("{$f} user may be half-logged in; about to check reauthentication key");
			}
			if ($user->authenticate($mysqli) !== SUCCESS) {
				if ($print) {
					Debug::print("{$f} user failed to reauthenticate; returning anonymous user data");
				}
				if (app()->hasUserData() && app()->getUserData()->getIdentifierValue() === $user->getIdentifierValue()) {
					app()->setUserData(null);
				}
				$this->anonymize();
				return SUCCESS;
			}
			if (cache()->enabled() && USER_CACHE_ENABLED) {
				$user_key = $user->getIdentifierValue();
				$ttl = $user->setTimeToLive(SESSION_TIMEOUT_SECONDS);
				if ($print) {
					Debug::print("{$f} TTL is {$ttl}");
				}
				if (cache()->hasAPCu($user_key)) {
					if ($print) {
						Debug::print("{$f} refreshing APCu cache TTL for user with key \"{$user_key}\"");
					}
					cache()->expireAPCu($user_key, $ttl);
				} else {
					if ($print) {
						Debug::print("{$f} cache does not have an entry for \"{$user_key}\"");
					}
					if ($user->hasCacheValue()) {
						if ($print) {
							Debug::print("{$f} user has a cache value");
						}
						cache()->setAPCu($user_key, $user->getCacheValue(), $ttl);
					} else {
						Debug::error("{$f} user does not have a cache value");
					}
				}
			} elseif ($print) {
				Debug::print("{$f} user cache is disabled, skipping user TTL refresh");
			}
			if ($print) {
				$pk = $user->getColumnValue("privateKey");
				if ($pk == null) {
					Debug::error("{$f} user lacks a private key");
				}
			}
			$status = $user->loadForeignDataStructures($mysqli, false);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} loadForeignDataStructures returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return $status; // $user;
			}
			app()->setUserData($user);
			// $user->setLoadout($this->getPredecessor()->generateRootLoadout($user));
			if ($print) {
				Debug::print("{$f} user data initialized, have fun");
			}
			// update last seen timestamp for this user. If you are logging out as admin the connection must be done now because once the log out completes it will be impossible to update your row with public write credentials
			$directive = directive();
			if ($directive === DIRECTIVE_LOGOUT && $user instanceof AuthenticatedUser) {
				if ($user instanceof Administrator) {
					if ($print) {
						Debug::print("{$f} user is logging out as administrator, loading credentials now");
					}
					$credentials = AdminWriteCredentials::class;
				} elseif ($user instanceof NormalUser) {
					if ($print) {
						Debug::print("{$f} user is logging out as a normal user");
					}
					$credentials = PublicWriteCredentials::class;
				}
				$mysqli = db()->reconnect($credentials);
			} elseif ($print) {
				Debug::print("{$f} user is not logging out as a registered user");
			}
			if(app()->hasWorkflow()){
				app()->getWorkflow()->addEventListener(EVENT_AFTER_RESPOND, function (AfterRespondEvent $event, Workflow $target) use ($f, $print, $user) {
					if ($user instanceof Administrator) {
						if ($print) {
							Debug::print("{$f} user is registered");
						}
						if (directive() === DIRECTIVE_LOGOUT) {
							$mysqli = db()->getConnection();
						} else {
							if ($print) {
								Debug::print("{$f} admin is not logging out, loading credentials");
							}
							$credentials = AdminWriteCredentials::class;
							$mysqli = db()->reconnect($credentials);
						}
					} else {
						$mysqli = db()->getConnection(PublicWriteCredentials::class);
					}
					global $__START;
					$user->updateLastSeenTimestamp($mysqli, $__START);
				});
			}else{
				Debug::warning("{$f} application runtime does not know about workflow. This should almost never happen");
			}
			if ($print) {
				Debug::print("{$f} returning normally with an object of class \"{$ucn}\"");
			}
			$user->setObjectStatus(SUCCESS);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return false;
	}

	public function getActionAttribute(): ?string
	{
		return ErrorMessage::unimplemented(f(static::class));
	}

	protected function getExecutePermissionClass()
	{
		return ErrorMessage::unimplemented(f(static::class));
	}

	public function getUseCaseId()
	{
		return USE_CASE_AUTHENTICATE;
	}
}
