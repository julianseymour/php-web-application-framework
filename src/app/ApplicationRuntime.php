<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_file_line;
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
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Debugger;
use JulianSeymour\PHPWebApplicationFramework\data\Repository;
use JulianSeymour\PHPWebApplicationFramework\db\DatabaseManager;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LazyLoadHelper;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseResponseEvent;
use JulianSeymour\PHPWebApplicationFramework\notification\push\PushNotifier;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

/**
 * this class contains high level functionality defining behavior that encompasses the entire web
 * application, not specific to any particular UseCase, as well as runtime variables shared between
 * UseCases.
 * it basically holds global variables
 *
 * @author j
 */
class ApplicationRuntime extends Basic{

	//use FlagBearingTrait;

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

	protected $repositories;
	
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
		$f = __METHOD__;
		$print = false;
		$this->setAllocatedFlag(true);
		if(DEBUG_MODE_ENABLED){
			if($print){
				Debug::print("{$f} debug flag is set");
			}
			$this->setDebugId(sha1(random_bytes(32)));
			$this->setDeclarationLine(get_file_line(["__construct"], 7));
			if(DEBUG_REFERENCE_MAPPING_ENABLED){
				$debugger = $this->getDebugger();
				$debugger->retain($this);
			}
		}elseif($print){
			Debug::print("{$f} debug mode is disabled`");
		}
		if($config !== null){
			$this->setConfiguration($config);
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
		if(!$this->hasGlobalScope()){
			if($print){
				Debug::print("{$f} instantiating a new global scope");
			}
			return $this->setGlobalScope(new Scope());
		}
		return $this->globalScope;
	}

	public function setGlobalScope(?Scope $scope):?Scope{
		if($this->hasGlobalScope()){
			$this->release($this->globalScope);
		}
		return $this->globalScope = $this->claim($scope);
	}

	public function hasEntryPoint(): bool{
		return isset($this->entryPoint) && $this->entryPoint instanceof UseCase;
	}

	public function setEntryPoint(?UseCase $entry_point): ?UseCase{
		if($this->hasEntryPoint()){
			$this->release($this->entryPoint);
		}
		return $this->entryPoint = $this->claim($entry_point);
	}

	public function setPushNotifier(?PushNotifier $pm): ?PushNotifier{
		if($this->hasPushNotifier()){
			$this->release($this->pushNotifier);
		}
		return $this->pushNotifier = $this->claim($pm);
	}

	public function hasPushNotifier(): bool{
		return isset($this->pushNotifier);
	}

	public function getPushNotifier(): PushNotifier{
		if(!$this->hasPushNotifier()){
			return $this->setPushNotifier(new PushNotifier());
		}
		return $this->pushNotifier;
	}

	public function setConfiguration(?ApplicationConfiguration $config): ?ApplicationConfiguration{
		if($this->hasConfiguration()){
			$this->release($this->configuration);
		}
		return $this->configuration = $this->claim($config);
	}

	public function hasConfiguration(): bool{
		return isset($this->configuration);
	}

	public function getConfiguration(): ApplicationConfiguration{
		$f = __METHOD__;
		if(!$this->hasConfiguration()){
			Debug::error("{$f} configuration is undefined");
		}
		return $this->configuration;
	}

	public function setModuleBundler(?ModuleBundler $bundler): ?ModuleBundler{
		if($this->hasModuleBundler()){
			$this->release($this->moduleBundler);
		}
		return $this->moduleBundler = $this->claim($bundler);
	}

	public function hasModuleBundler(): bool{
		return isset($this->moduleBundler);
	}

	public function getModuleBundler(): ModuleBundler{
		$f = __METHOD__;
		if(!$this->hasModuleBundler()){
			Debug::error("{$f} module bundler is undefined");
		}
		return $this->moduleBundler;
	}

	/**
	 * Don't claim/release the Debugger
	 * @param Debugger $debugger
	 * @return Debugger|NULL
	 */
	public function setDebugger(?Debugger $debugger): ?Debugger{
		return $this->debugger = $debugger;
	}

	public function hasDebugger(): bool{
		return isset($this->debugger);
	}

	public function getDebugger(): Debugger{
		$f = __METHOD__;
		if(!$this->hasDebugger()){
			return $this->setDebugger(new Debugger());
			//Debug::error("{$f} debugger is undefined");
		}
		return $this->debugger;
	}

	public function hasRegistry():bool{
		return isset($this->registry);
	}

	public function getRegistry(): Registry{
		if($this->hasRegistry()){
			return $this->registry;
		}
		return $this->setRegistry(new Registry());
	}

	public function setRegistry(?Registry $reg):?Registry{
		if($this->hasRegistry()){
			$this->release($this->registry);
		}
		return $this->registry = $this->claim($reg);
	}
	
	public function hasLazyLoadHelper(): bool{
		return isset($this->lazyLoadHelper);
	}

	public function getLazyLoadHelper(): LazyLoadHelper{
		if($this->hasLazyLoadHelper()){
			return $this->lazyLoadHelper;
		}
		return $this->setLazyLoadHelper(new LazyLoadHelper());
	}

	public function setLazyLoadHelper(?LazyLoadHelper $llh):?LazyLoadHelper{
		if($this->hasLazyLoadHelper()){
			$this->release($this->lazyLoadHelper);
		}
		return $this->lazyLoadHelper = $this->claim($llh);
	}
	
	public function hasDatabaseManager(): bool{
		return isset($this->databaseManager);
	}

	public function getDatabaseManager(): DatabaseManager{
		if($this->hasDatabaseManager()){
			return $this->databaseManager;
		}
		return $this->setDatabaseManager(new DatabaseManager());
	}

	public function setDatabaseManager(?DatabaseManager $dbm):?DatabaseManager{
		if($this->hasDatabaseManager()){
			$this->release($this->databaseManager);
		}
		return $this->databaseManager = $this->claim($dbm);
	}
	
	public function releaseUserData(bool $deallocate=false){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasUserData()){
			Debug::error("{$f} user data is undefined");
		}
		$user = $this->userData;
		unset($this->userData);
		if($deallocate){
			if($user->getDisableDeallocationFlag()){
				$user->enableDeallocation();
			}
			$user->protectColumns(false);
		}
		if($print){
			Debug::print("{$f} about to release user data ".$user->getDebugString());
		}
		$this->release($user, $deallocate);
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
		$this->claim($user);
		if($this->hasUserData()){
			if($print){
				Debug::print("{$f} user data ".$this->userData->getDebugString()." is already assigned, releasing it now in favor of ".$user->getDebugString());
			}
			$this->releaseUserData(true);
		}elseif($print){
			Debug::print("{$f} assigning user data ".$user->getDebugString()." for this first time");
		}
		if(!$user->getAllocatedFlag()){
			Debug::error("{$f} allocated flag is not set on line 405 for ".$user->getDebugString());
		}elseif(!$user->hasColumns()){
			Debug::error("{$f} user ".$user->getDebugString()." has no columns on line 407");
		}
		if(!$user instanceof UserData){
			Debug::error("{$f} user is not a UserData");
		}elseif(!$user->getAllocatedFlag()){
			Debug::error("{$f} allocated flag is not set on line 412 for ".$user->getDebugString());
		}elseif($user->getAllocationMode() !== ALLOCATION_MODE_SUBJECTIVE){
			$decl = $user->getDeclarationLine();
			Debug::error("{$f} incorrect allocation mode; declared {$decl}");
		}elseif(!$user->hasColumns()){
			Debug::error("{$f} user ".$user->getDebugString()." has no columns on line 417");
		}elseif($user->hasIdentifierValue()){
			$key = $user->getIdentifierValue();
			if(!$this->getRegistry()->hasObjectRegisteredToKey($key)){
				$this->getRegistry()->registerObjectToKey($key, $user);
			}
		}elseif(!$user instanceof AnonymousUser){
			Debug::error("{$f} user lacks a key");
		}
		if($user instanceof AuthenticatedUser && !$user->hasEmailAddress()){
			Debug::error("{$f} authenticated user lacks an email address");
		}elseif($print){
			$ds = $user->getDebugString();
			Debug::print("{$f} setting user data to a {$ds}");
		}
		$user->disableDeallocation();
		if(!$user->hasColumns()){
			Debug::error("{$f} user ".$user->getDebugString()." has no columns on line 434");
		}
		$user->protectColumns();
		return $this->userData = $user;
	}

	public function hasUserData(): bool{
		return isset($this->userData);
	}

	public function hasResponse(): bool{
		return isset($this->response);
	}

	public function setResponse(?XMLHttpResponse $response): ?XMLHttpResponse	{
		$f = __METHOD__;
		if(!$response instanceof XMLHttpResponse){
			Debug::error("{$f} response is something other than an XML HTTP response object");
		}elseif($this->hasResponse()){
			$this->release($this->response);
		}
		$random = sha1(random_bytes(32));
		$that = $this;
		$closure1 = function(DeallocateEvent $event, XMLHttpResponse $target) use ($that, $random){
			$target->removeEventListener($event);
			if($that->hasEventListener(EVENT_RELEASE_RESPONSE, $random)){
				$that->removeEventListener(EVENT_RELEASE_RESPONSE, $random);
			}
			if($that->hasResponse()){
				$that->releaseResponse();
			}
		};
		$response->addEventListener(EVENT_DEALLOCATE, $closure1, $random);
		$closure2 = function(ReleaseResponseEvent $event, ApplicationRuntime $target) use ($response, $random){
			$target->removeEventListener($event);
			if($response->hasEventListener(EVENT_DEALLOCATE, $random)){
				$response->removeEventListener(EVENT_DEALLOCATE, $random);
			}
		};
		$this->addEventListener(EVENT_RELEASE_RESPONSE, $closure2, $random);
		return $this->response = $this->claim($response);
	}

	public function hasRequest(): bool{
		return isset($this->request);
	}

	/**
	 *
	 * @return NormalUser|Administrator|AnonymousUser
	 */
	public function getUserData(): ?PlayableUser{
		$f = __METHOD__;
		if(!$this->hasUserData()){
			Debug::error("{$f} user data is undefined");
		}
		return $this->userData;
	}

	/**
	 *
	 * @return ServerKeypair
	 */
	public function setCurrentServerKeypair($kp){
		if($this->hasCurrentServerKeypair()){
			$this->release($this->currentServerKeypair);
		}
		return $this->currentServerKeypair = $this->claim($kp);
	}

	/**
	 *
	 * @return ServerKeypair
	 */
	public function getCurrentServerKeypair(){
		$f = __METHOD__;
		if(!$this->hasCurrentServerKeypair()){
			Debug::error("{$f} current server keypair is undefined");
		}
		return $this->currentServerKeypair;
	}

	public function hasCurrentServerKeypair(): bool{
		return isset($this->currentServerKeypair);
	}

	public function acquireCurrentServerName(mysqli $mysqli): string{
		$f = __METHOD__;
		try{
			if(!$this->hasCurrentServerKeypair()){
				$skp = $this->acquireCurrentServerKeypair($mysqli);
			}else{
				$skp = $this->getCurrentServerKeypair();
			}
			return $skp->getName();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasExecutionState(): bool{
		return isset($this->executionState) && is_int($this->executionState);
	}

	public function advanceExecutionState(int $state): int{
		$f = __METHOD__;
		if(!$this->hasExecutionState() || $state > $this->executionState){
			mark("Advancing execution state to {$state}");
			return $this->executionState = $state;
		}
		Debug::limitExecutionDepth(255);
		Debug::printStackTraceNoExit("{$f} repeat/regressive execution state \"{$state}\"");
		return $this->executionState;
	}

	public function getExecutionState(): ?int{
		$f = __METHOD__;
		if(!$this->hasExecutionState()){
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
		try{
			$print = false;
			if(isset($this->currentServerKeypair)){
				return $this->currentServerKeypair;
			}elseif($this->getFlag("noKeypair")){
				Debug::print("{$f} no keypair");
				return null;
			}elseif(!$mysqli instanceof mysqli){
				$mysqli = db()->getConnection(PublicReadCredentials::class);
				if($mysqli == null){
					Debug::warning("{$f} mysqli object is null");
					$this->setFlag("noKeypair", true);
					return null;
				}
			}
			$kp = new ServerKeypair();
			$status = $kp->load($mysqli, "isCurrentServer", 1);
			if($status === ERROR_NOT_FOUND){
				Debug::error("{$f} current keypair not found");
			}elseif($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} keypair has error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}elseif($print){
				$name = $kp->getName();
				Debug::print("{$f} returning server \"{$name}\"");
			}
			return $this->setCurrentServerKeypair($kp);
		}catch(Exception $x){
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
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered");
			}
			if(push()->hasQueue()){
				if($print){
					Debug::print("{$f} push notification queue is not empty");
				}
				$status = push()->transmitQueue();
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} transmitPushNotificationQueue() returned error status \"{$err}\"");
				}
			}elseif($print){
				Debug::print("{$f} push notification queue is empty");
			}
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasCache(): bool{
		return isset($this->cache);
	}

	public function getCache(): MultiCache{
		if($this->hasCache()){
			return $this->cache;
		}
		return $this->setCache(new MultiCache());
	}

	public function setCache(?MultiCache $cache):?MultiCache{
		if($this->hasCache()){
			$this->release($this->cache);
		}
		return $this->cache = $this->claim($cache);
	}
	
	
	public function getResponse(?UseCase $use_case = null): XMLHttpResponse{
		if($this->hasResponse()){
			return $this->response;
		}
		return $this->setResponse(new XMLHttpResponse());
	}

	public function setRequest(?Request $request): ?Request{
		if($this->hasRequest()){
			$this->release($this->request);
		}
		return $this->request = $this->claim($request);
	}

	public function getRequest(): ?Request{
		$f = __METHOD__;
		if(!$this->hasRequest()){
			Debug::error("{$f} request is undefined");
		}
		return $this->request;
	}

	public function getUseCase(): ?UseCase{
		$f = __METHOD__;
		if(!$this->hasUseCase()){
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
		if(!$use_case instanceof UseCase){
			Debug::error("{$f} use case is not a use case");
		}elseif($print){
			$ucc = $use_case->getClass();
			Debug::printStackTraceNoExit("{$f} setting use case to \"{$ucc}\"");
		}
		if($this->hasUseCase()){
			$this->release($this->useCase);
		}
		return $this->useCase = $this->claim($use_case);
	}

	public function setWorkflow(?Workflow $workflow): ?Workflow{
		if($this->hasWorkflow()){
			$this->release($this->workflow);
		}
		return $this->workflow = $this->claim($workflow);
	}

	public function hasWorkflow(): bool{
		return isset($this->workflow);
	}

	public function getWorkflow(): Workflow{
		$f = __METHOD__;
		if(!$this->hasWorkflow()){
			Debug::error("{$f} workflow is undefined");
		}
		return $this->workflow;
	}
	
	public function releaseResponse(bool $deallocate=false):void{
		$f = __METHOD__;
		if(!$this->hasResponse()){
			Debug::error("{$f} response is undefined");
		}
		$response = $this->response;
		unset($this->response);
		if($this->hasAnyEventListener(EVENT_RELEASE_RESPONSE)){
			$this->dispatchEvent(new ReleaseResponseEvent($response, $deallocate));
		}
		$this->release($response, $deallocate);
	}
	
	public function hasRepository(string $database, string $table):bool{
		return isset($this->repositories)
		&& is_array($this->repositories)
		&& array_key_exists($database, $this->repositories)
		&& is_array($this->repositories[$database])
		&& array_key_exists($table, $this->repositories[$database]);
	}
	
	public function releaseRepository(string $database, string $table, bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasRepository($database, $table)){
			Debug::error("{$f} no repository for table `{$database}`.`{$table}`");
		}
		$repo = $this->repositories[$database][$table];
		unset($this->repositories[$database][$table]);
		if(empty($this->repositories[$database])){
			unset($this->repositories[$database]);
			if(empty($this->repositories)){
				unset($this->repositories);
			}
		}
		$this->release($repo, $deallocate);
	}
	
	public function hasRepositories():bool{
		return isset($this->repositories)
		&& is_array($this->repositories)
		&& !empty($this->repositories);
	}
	
	public function getRepositories():array{
		$f = __METHOD__;
		if(!$this->hasRepositories()){
			Debug::error("{$f} no repositories");
		}
		return $this->repositories;
	}
	
	public function releaseRepositories(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasRepositories()){
			Debug::error("{$f} no repositories");
		}
		foreach($this->getRepositories() as $database => $tables){
			foreach(array_keys($tables) as $table){
				$this->releaseRepository($database, $table, $deallocate);
			}
		}
	}
	
	public function setRepository(string $database, string $table, ?Repository $repository):?Repository{
		if($this->hasRepository($database, $table)){
			$this->releaseRepository($database, $table);
		}
		if(!isset($this->repositories) || !is_array($this->repositories)){
			$this->repositories = [];
		}
		if(!isset($this->repositories[$database]) || !is_array($this->repositories[$database])){
			$this->repositories[$database] = [];
		}
		return $this->repositories[$database][$table] = $this->claim($repository);
	}
	
	public function getRepository(string $database, string $table):Repository{
		if($this->hasRepository($database, $table)){
			return $this->repositories[$database][$table];
		}
		$repo = new Repository();
		$repo->setDatabaseName($database);
		$repo->setTableName($table);
		return $this->setRepository($database, $table, $repo);
	}
	
	public function dispose(bool $deallocate=false):void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($print){
			Debug::print("{$f} entered, about to call parent function");
		}
		parent::dispose($deallocate);
		if($print){
			Debug::print("{$f} made it back from parent function. About to release cache");
		}
		if($deallocate){
			deallocate($this->cache); 
			if($this->hasCache()){
				Debug::error("{$f} cache should be completely undefined at this point");
			}elseif($print){
				Debug::print("{$f} cache is undefined, as it should be");
			}
			deallocate($this->configuration);
			deallocate($this->currentServerKeypair);
			deallocate($this->databaseManager);
			deallocate($this->entryPoint);
			deallocate($this->executionState);
			deallocate($this->globalScope);
			deallocate($this->lazyLoadHelper);
			deallocate($this->moduleBundler);
			deallocate($this->pushNotifier);
			deallocate($this->registry);
			if($this->hasRepositories()){
				$this->releaseRepositories($deallocate);
			}
			deallocate($this->request);
			if($this->hasResponse()){
				$this->releaseResponse($deallocate);
			}
			deallocate($this->useCase);
			if($this->hasUserData()){
				$this->releaseUserData($deallocate);
			}elseif($print){
				Debug::print("{$f} no user data to release");
			}
			if($print){
				Debug::print("{$f} returned from releasing user data, about to release workflow");
			}
			deallocate($this->workflow); 
		}else{
			$ds = $this->getDebugString();
			unset($this->cache, $deallocate, $ds);
			if($this->hasCache()){
				Debug::error("{$f} cache should be completely undefined at this point");
			}elseif($print){
				Debug::print("{$f} cache is undefined, as it should be");
			}
			unset($this->configuration);
			unset($this->currentServerKeypair);
			unset($this->databaseManager);
			unset($this->entryPoint);
			unset($this->executionState);
			unset($this->globalScope);
			unset($this->lazyLoadHelper);
			unset($this->moduleBundler);
			unset($this->pushNotifier);
			unset($this->registry);
			unset($this->repositories);
			unset($this->request);
			unset($this->response);
			unset($this->useCase);
			if($this->hasUserData()){
				if($print){
					Debug::print("{$f} about to release user data");
					if($deallocate){
						Debug::print("{$f} hard deallocate");
					}else{
						Debug::error("{$f} user data should not be defined if we are soft disposing");
					}
				}
				unset($this->userData);
			}
			if($print){
				Debug::print("{$f} returned from releasing user data, about to release workflow");
			}
			unset($this->workflow);
		}
		if($print){
			Debug::print("{$f} successfully made it to the end of the function");
		}
	}
}
