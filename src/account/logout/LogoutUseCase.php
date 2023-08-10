<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\logout;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticateUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\PreauthenticationUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadTreeUseCase;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\session\hijack\AntiHijackSessionData;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryCookie;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use Exception;

class LogoutUseCase extends PreauthenticationUseCase implements ClientUseCaseInterface, JavaScriptCounterpartInterface
{

	use JavaScriptCounterpartTrait;

	public function __construct($predecessor = null, $segments = null)
	{
		$f = __METHOD__;
		if ($predecessor instanceof LogoutUseCase) {
			Debug::error("{$f} precedessor cannot be another LogoutUseCase");
		}
		return parent::__construct($predecessor, $segments);
	}

	public function logout($mysqli)
	{
		$f = __METHOD__;
		try {
			$print = false;
			if (app()->hasUserData()) {
				$timestamp = time();
				$user = app()->getUserData();
				// $user->unsetFilteredColumns("cookie");
				if ($user instanceof AuthenticatedUser) {
					if ($user instanceof Administrator) {
						if($user->hasColumnValue("privateKey")){
							db()->disconnect();
							$mysqli = db()->getConnection(AdminWriteCredentials::class);
						}else{
							$mysqli = db()->getConnection(PublicWriteCredentials::class);
						}
					}
					$user->updateLogoutTimestamp($mysqli, $timestamp);
				}
			} elseif ($print) {
				Debug::print("{$f} application lacks user data");
			}
			$recovery_cookie = new SessionRecoveryCookie();
			if ($recovery_cookie->hasRecoveryKey()) {
				if ($print) {
					Debug::print("{$f} about to delete session recovery data");
				}
				$recovery_cookie->deleteSession();
			} elseif ($print) {
				Debug::print("{$f} recovery cookie lacks a recovery key");
			}
			if ($print) {
				Debug::warning("{$f} destroying session");
			}
			// $anon = new FullAuthenticationData();
			// $key = $anon->getUserKey();
			foreach ([
				PreMultifactorAuthenticationData::class,
				FullAuthenticationData::class,
				AntiHijackSessionData::class
			] as $class) {
				$class::unsetColumnValuesStatic();
			}
			$user = AuthenticateUseCase::getAnonymousUser();
			/*
			 * $loadout = $this->generateRootLoadout($user);
			 * if($loadout instanceof Loadout){
			 * $user->set/Loadout($loadout);
			 * }
			 */
			return app()->setUserData($user);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function execute(): int
	{
		$f = __METHOD__;
		$print = false;
		if (app()->hasUserData()) {
			if ($print) {
				Debug::print("{$f} application instance has user data");
			}
			$user = user();
			if ($user instanceof AuthenticatedUser) {
				$class = $user->getClass();
				if ($print) {
					Debug::print("{$f} user class is \"{$class}\" about to connect public updater");
				}
				$mysqli = db()->getConnection(PublicWriteCredentials::class);
				if (! isset($mysqli)) {
					Debug::error("{$f} mysqli connection returned null");
				}
				// $session = new FullAuthenticationData();
				// $session->ejectDeterministicSecretKey();
				$this->logout($mysqli);
			} else {
				if ($print) {
					Debug::print("{$f} user data is not registered so fuck em");
				}
				$mysqli = db()->getConnection(PublicReadCredentials::class);
				if ($print) {
					Debug::print("{$f} user is a guest");
				}
			}
		} else {
			if ($print) {
				Debug::print("{$f} application instance lacks a user data");
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
		}
		$auth = new AuthenticateUseCase($this);
		$auth->validateTransition();
		$auth->execute();
		$load = new LoadTreeUseCase($this);
		$load->validateTransition();
		$load->execute();
		/*
		 * $user = $this->authenticate();
		 * if(!isset($mysqli)){
		 * Debug::error("{$f} mysqli is undefined");
		 * }
		 * $this->load($mysqli);
		 */
		return $this->setObjectStatus(RESULT_LOGGED_OUT);
	}

	public function prerender()
	{
		return SUCCESS;
	}

	public function getActionAttribute(): ?string
	{
		return "/logout";
	}

	public function getUseCaseId()
	{
		return USE_CASE_LOGOUT;
	}

	public function getAuthenticatedUserClass()
	{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getResponder(): ?Responder
	{
		if ($this->getObjectStatus() === RESULT_LOGGED_OUT) {
			return new LogoutResponder();
		}
		return parent::getResponder();
	}

	public function getClientUseCaseName(): ?string
	{
		return "logout";
	}
}
