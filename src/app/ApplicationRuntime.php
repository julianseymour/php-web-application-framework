<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\mark;
use function JulianSeymour\PHPWebApplicationFramework\push;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\Workflow;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\cache\MultiCache;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Debugger;
use JulianSeymour\PHPWebApplicationFramework\db\DatabaseManager;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LazyLoadHelper;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\push\PushNotifier;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\element\HTMLElement;

/**
 * this class contains high level functionality defining behavior that encompasses the entire web
 * application, not specific to any particular UseCase, as well as runtime variables shared between
 * UseCases.
 * it basically holds global variables
 *
 * @author j
 */
class ApplicationRuntime extends Basic{

	use FlagBearingTrait;

	/**
	 *
	 * @var MultiCache
	 */
	protected $cache;

	/**
	 *
	 * @var ApplicationConfiguration
	 */
	protected $configuration;

	/**
	 * keypair for the server the application is executing on
	 *
	 * @var ServerKeypair
	 */
	protected $currentServerKeypair;

	/**
	 *
	 * @var DatabaseManager
	 */
	protected $databaseManager;

	/**
	 *
	 * @var Debugger
	 */
	protected $debugger;

	/**
	 * 
	 * @var HTMLElement
	 */
	//protected $documentRoot;
	
	/**
	 *
	 * @var UseCase
	 */
	protected $entryPoint;

	/**
	 * keeps track of what phase of the request/response cycle we're currently in
	 *
	 * @var int
	 */
	protected $executionState;

	/**
	 * needed for executing commands
	 *
	 * @var Scope
	 */
	protected $globalScope;

	/**
	 *
	 * @var LazyLoadHelper
	 */
	protected $lazyLoadHelper;

	/**
	 * extracts data from modules
	 *
	 * @var ModuleBundler
	 */
	protected $moduleBundler;

	/**
	 *
	 * @var PushNotifier
	 */
	protected $pushNotifier;

	/**
	 *
	 * @var Registry
	 */
	protected $registry;

	/**
	 * object representing the incoming request
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * object that is returned as part of a fetch/XHR response.
	 * Non-JS responses return regular HTML.
	 *
	 * @var XMLHttpResponse
	 */
	protected $response;

	/**
	 *
	 * @var object
	 */
	protected $themeManager;

	/**
	 * the use case that is currently controlling program flow
	 *
	 * @var UseCase
	 */
	protected $useCase;

	/**
	 * data structure of the user who initiated the request
	 *
	 * @var PlayableUser
	 */
	protected $userData;

	/**
	 *
	 * @var Workflow
	 */
	protected $workflow;

	public function __construct(?ApplicationConfiguration $config = null){
		if ($config !== null) {
			$this->setConfigration($config);
		}
		$this->executionState = EXECUTION_STATE_INITIAL;
		return;
	}

	public static function declareFlags(): ?array{
		return [
			"debug",
			"debugCrypt",
			"debugQuery",
			"forbidGuest",
			"forbidResumeSession",
			"noKeypair",
			"resumedSession",
			"validIpAddress"
		];
	}

	public function hasGlobalScope(): bool{
		return isset($this->globalScope);
	}

	public function getGlobalScope(): Scope{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasGlobalScope()) {
			if ($print) {
				Debug::print("{$f} instantiating a new global scope");
			}
			return $this->setGlobalScope(new Scope());
		}
		return $this->globalScope;
	}

	public function setGlobalScope(?Scope $scope): ?Scope{
		if ($scope === null) {
			unset($this->globalScope);
			return null;
		}
		return $this->globalScope = $scope;
	}

	public function hasEntryPoint(): bool{
		return isset($this->entryPoint) && $this->entryPoint instanceof UseCase;
	}

	public function setEntryPoint(?UseCase $entry_point): ?UseCase{
		if ($entry_point === null) {
			unset($this->entryPoint);
			return null;
		}
		return $this->entryPoint = $entry_point;
	}

	public function setPushNotifier(?PushNotifier $pm): ?PushNotifier{
		if ($pm === null) {
			unset($this->pushNotifier);
			return null;
		}
		return $this->pushNotifier = $pm;
	}

	public function hasPushNotifier(): bool{
		return isset($this->pushNotifier) && $this->pushNotifier instanceof PushNotifier;
	}

	public function getPushNotifier(): PushNotifier{
		if (! $this->hasPushNotifier()) {
			return $this->setPushNotifier(new PushNotifier());
		}
		return $this->pushNotifier;
	}

	public function setConfigration(?ApplicationConfiguration $config): ?ApplicationConfiguration{
		if ($config === null) {
			unset($this->configuration);
			return null;
		}
		return $this->configuration = $config;
	}

	public function hasConfiguration(): bool{
		return isset($this->configuration) && $this->configuration instanceof ApplicationConfiguration;
	}

	public function getConfiguration(): ApplicationConfiguration{
		$f = __METHOD__;
		if (! $this->hasConfiguration()) {
			Debug::error("{$f} configration is undefined");
		}
		return $this->configuration;
	}

	public function setModuleBundler(?ModuleBundler $bundler): ?ModuleBundler{
		if ($bundler === null) {
			unset($this->moduleBundler);
			return null;
		}
		return $this->moduleBundler = $bundler;
	}

	public function hasModuleBundler(): bool{
		return isset($this->moduleBundler) && $this->moduleBundler instanceof ModuleBundler;
	}

	public function getModuleBundler(): ModuleBundler{
		$f = __METHOD__;
		if (! $this->hasModuleBundler()) {
			Debug::error("{$f} module bundler is undefined");
		}
		return $this->moduleBundler;
	}

	public function setDebugger(?Debugger $debugger): ?Debugger{
		if ($debugger === null) {
			unset($this->debugger);
			return null;
		}
		return $this->debugger = $debugger;
	}

	public function hasDebugger(): bool{
		return isset($this->debugger) && $this->debugger instanceof Debugger;
	}

	public function getDebugger(): Debugger{
		$f = __METHOD__;
		if (! $this->hasDebugger()) {
			return $this->setDebugger(new Debugger());
			Debug::error("{$f} debugger is undefined");
		}
		return $this->debugger;
	}

	public function hasRegistry(): bool{
		return isset($this->registry) && $this->registry instanceof Registry;
	}

	public function getRegistry(): Registry{
		if ($this->hasRegistry()) {
			return $this->registry;
		}
		return $this->registry = new Registry();
	}

	public function hasLazyLoadHelper(): bool{
		return isset($this->lazyLoadHelper) && $this->lazyLoadHelper instanceof LazyLoadHelper;
	}

	public function getLazyLoadHelper(): LazyLoadHelper{
		if ($this->hasLazyLoadHelper()) {
			return $this->lazyLoadHelper;
		}
		return $this->lazyLoadHelper = new LazyLoadHelper();
	}

	public function hasDatabaseManager(): bool{
		return isset($this->databaseManager) && $this->databaseManager instanceof DatabaseManager;
	}

	public function getDatabaseManager(): DatabaseManager{
		if ($this->hasDatabaseManager()) {
			return $this->databaseManager;
		}
		return $this->databaseManager = new DatabaseManager();
	}

	/**
	 * sets the data structure for user who initiated the request
	 *
	 * @param UserData $data
	 * @return UserData
	 */
	public function setUserData(?PlayableUser $user): ?PlayableUser{
		$f = __METHOD__;
		$print = false;
		if ($user === null) {
			unset($this->userData);
			return null;
		} elseif (! $user instanceof UserData) {
			Debug::error("{$f} user is not a UserData");
		} elseif ($user->getAllocationMode() !== ALLOCATION_MODE_SUBJECTIVE) {
			$decl = $user->getDeclarationLine();
			Debug::error("{$f} incorrect allocation mode; declared {$decl}");
		} elseif ($user->hasIdentifierValue()) {
			$key = $user->getIdentifierValue();
			if (! $this->getRegistry()->hasObjectRegisteredToKey($key)) {
				$this->getRegistry()->registerObjectToKey($key, $user);
			}
		} elseif (! $user instanceof AnonymousUser) {
			Debug::error("{$f} non-anonymous user lacks a key");
		}
		if ($user instanceof AuthenticatedUser && ! $user->hasEmailAddress()) {
			Debug::error("{$f} authenticated user lacks an email address");
		}
		if ($print) {
			$uc = $user->getClass();
			$key = $user->hasIdentifierValue() ? $user->getIdentifierValue() : "undefined";
			Debug::printStackTraceNoExit("{$f} setting user data to a {$uc} with key \"{$key}\"");
		}
		return $this->userData = $user;
	}

	public function hasUserData(): bool{
		return isset($this->userData);
	}

	public function hasResponse(): bool{
		return isset($this->response) && $this->response instanceof XMLHttpResponse;
	}

	public function setResponse(?XMLHttpResponse $response): ?XMLHttpResponse	{
		$f = __METHOD__;
		if (! $response instanceof XMLHttpResponse) {
			Debug::error("{$f} response is something other than an XML HTTP response object");
		}
		return $this->response = $response;
	}

	public function hasRequest(): bool{
		return isset($this->request) && $this->request instanceof Request;
	}

	/**
	 *
	 * @return NormalUser|Administrator|AnonymousUser
	 */
	public function getUserData(): ?PlayableUser{
		$f = __METHOD__;
		if (! $this->hasUserData()) {
			Debug::error("{$f} user data is undefined");
		}
		return $this->userData;
	}

	/**
	 *
	 * @return ServerKeypair
	 */
	public function setCurrentServerKeypair($kp){
		return $this->currentServerKeypair = $kp;
	}

	/**
	 *
	 * @return ServerKeypair
	 */
	public function getCurrentServerKeypair(){
		$f = __METHOD__;
		if (! $this->hasCurrentServerKeypair()) {
			Debug::error("{$f} current server keypair is undefined");
		}
		return $this->currentServerKeypair;
	}

	public function hasCurrentServerKeypair(): bool
	{
		return isset($this->currentServerKeypair) && is_object($this->currentServerKeypair);
	}

	public function acquireCurrentServerName(mysqli $mysqli): string{
		$f = __METHOD__;
		try {
			if (! $this->hasCurrentServerKeypair()) {
				$skp = $this->acquireCurrentServerKeypair($mysqli);
			} else {
				$skp = $this->getCurrentServerKeypair();
			}
			return $skp->getName();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasExecutionState(): bool{
		return isset($this->executionState) && is_int($this->executionState);
	}

	public function advanceExecutionState(int $state): int{
		$f = __METHOD__;
		if (! $this->hasExecutionState() || $state > $this->executionState) {
			mark("Advancing execution state to {$state}");
			return $this->executionState = $state;
		}
		Debug::limitExecutionDepth(255);
		Debug::printStackTraceNoExit("{$f} repeat/regressive execution state \"{$state}\"");
		return $this->executionState;
	}

	public function getExecutionState(): ?int{
		$f = __METHOD__;
		if (! $this->hasExecutionState()) {
			Debug::error("{$f} execution state is undefined");
		}
		return $this->executionState;
	}

	/**
	 *
	 * @return ServerKeypair
	 */
	public function acquireCurrentServerKeypair(?mysqli $mysqli = null){
		$f = __METHOD__;
		try {
			$print = false;
			if (isset($this->currentServerKeypair)) {
				return $this->currentServerKeypair;
			} elseif ($this->getFlag("noKeypair")) {
				Debug::print("{$f} no keypair");
				return null;
			} elseif (! $mysqli instanceof mysqli) {
				$mysqli = db()->getConnection(PublicReadCredentials::class);
				if ($mysqli == null) {
					Debug::warning("{$f} mysqli object is null");
					$this->setFlag("noKeypair", true);
					return null;
				}
			}
			$kp = new ServerKeypair();
			$status = $kp->load($mysqli, "isCurrentServer", 1);
			if ($status === ERROR_NOT_FOUND) {
				Debug::error("{$f} current keypair not found");
			} elseif ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} keypair has error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			} elseif ($print) {
				$name = $kp->getName();
				Debug::print("{$f} returning server \"{$name}\"");
			}
			return $this->setCurrentServerKeypair($kp);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * XXX TODO turn this into a use case to which normal use cases pass the baton
	 * this is for defering push notifications until after a message is confirmed sent, and writing to the backup server
	 *
	 * @return int
	 */
	public function dispatchCallbacks():int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			if (push()->hasQueue()) {
				if ($print) {
					Debug::print("{$f} push notification queue is not empty");
				}
				$status = push()->transmitQueue();
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} transmitPushNotificationQueue() returned error status \"{$err}\"");
				}
			} elseif ($print) {
				Debug::print("{$f} push notification queue is empty");
			}
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasCache(): bool{
		return isset($this->cache) && $this->cache instanceof MultiCache;
	}

	public function getCache(): MultiCache{
		if ($this->hasCache()) {
			return $this->cache;
		}
		return $this->cache = new MultiCache();
	}

	public function getResponse(?UseCase $use_case = null): XMLHttpResponse{
		if ($this->hasResponse()) {
			return $this->response;
		} elseif ($use_case === null) {
			$use_case = $this->getUseCase();
		}
		return $this->setResponse(new XMLHttpResponse());
	}

	public function setRequest(?Request $request): ?Request{
		if ($request === null) {
			unset($this->request);
			return null;
		}
		return $this->request = $request;
	}

	public function getRequest(): ?Request{
		$f = __METHOD__;
		if (! $this->hasRequest()) {
			Debug::error("{$f} request is undefined");
		}
		return $this->request;
	}

	public function getUseCase(): ?UseCase{
		$f = __METHOD__;
		if (! $this->hasUseCase()) {
			Debug::error("{$f} use case object is undefined");
		}
		return $this->useCase;
	}

	public function hasUseCase(): bool{
		return isset($this->useCase);
	}

	public function setUseCase(?UseCase $use_case): ?UseCase{
		$f = __METHOD__;
		$print = false;
		if (! $use_case instanceof UseCase) {
			Debug::error("{$f} use case is not a use case");
		} elseiF ($print) {
			$ucc = $use_case->getClass();
			Debug::printStackTraceNoExit("{$f} setting use case to \"{$ucc}\"");
		}
		return $this->useCase = $use_case;
	}

	public function setWorkflow(?Workflow $workflow): ?Workflow{
		if ($workflow === null) {
			unset($this->workflow);
			return null;
		}
		return $this->workflow = $workflow;
	}

	public function hasWorkflow(): bool{
		return isset($this->workflow) && $this->workflow instanceof Workflow;
	}

	public function getWorkflow(): Workflow{
		$f = __METHOD__;
		if (! $this->hasWorkflow()) {
			Debug::error("{$f} workflow is undefined");
		}
		return $this->workflow;
	}
	
	/*public function hasDocumentRoot():bool{
		return isset($this->documentRoot);
	}
	
	public function setDocumentRoot(?HTMLElement $root):?HTMLElement{
		if($root == null){
			unset($this->documentRoot);
			return null;
		}
		return $this->documentRoot = $root;
	}
	
	public function getDocumentRoot():HTMLElement{
		$f = __METHOD__;
		if(!$this->hasDocumentRoot()){
			Debug::error("{$f} document root is undefined");
		}
		return $this->documentRoot;
	}*/
}
