<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\correspondent\GetCorrespondentUseCase;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\ResponseHooksTrait;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkResponder;
use JulianSeymour\PHPWebApplicationFramework\auth\AfterAuthenticateEvent;
use JulianSeymour\PHPWebApplicationFramework\auth\BeforeAuthenticateEvent;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\common\DisabledFlagTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessageTrait;
use JulianSeymour\PHPWebApplicationFramework\error\FileNotFoundUseCase;
use JulianSeymour\PHPWebApplicationFramework\error\InternalServerErrorUseCase;
use JulianSeymour\PHPWebApplicationFramework\event\AfterExecuteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeExecuteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenData;
use JulianSeymour\PHPWebApplicationFramework\session\hijack\AntiHijackSessionData;
use JulianSeymour\PHPWebApplicationFramework\session\timeout\RefreshSessionTimeoutResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use Exception;
use mysqli;

/**
 * Handles program flow control for a specific task.
 * This includes authentication, automatically generating and loading hierarchical loadouts, and UseCase-specific headers, among other features. Roughly analagous to a controller in an MVC framework. UseCases can be linked together based on status code with the $witchUseCases property; The main entry point should be handleRequest() for UseCases that initiate processing a request/response cycle, execute() for those that will take control of program execution flow until the response has been sent, or execute() for helper classes which do nothing outside of that function
 *
 * @author j
 */
abstract class UseCase extends ProgramFlowControlUnit implements JavaScriptCounterpartInterface{

	use ArrayPropertyTrait;
	use DisabledFlagTrait;
	use ErrorMessageTrait;
	use EventListeningTrait;
	use JavaScriptCounterpartTrait;
	use PermissiveTrait;
	use ResponseHooksTrait;
	
	protected $bruteforceAttemptObject;

	protected $dataOperandObject;

	protected $loadoutGenerator;
	
	protected $predecessor;

	protected $switchUseCases;

	public abstract function getActionAttribute(): ?string;

	protected abstract function getExecutePermissionClass();

	public function __construct(?UseCase $predecessor = null){
		$f = __METHOD__;
		try{
			parent::__construct();
			$this->setPermission(DIRECTIVE_TRANSITION_FROM, $this->getTransitionFromPermission());
			if(isset($predecessor)) {
				$this->setPredecessor($predecessor);
			}
			$this->setPermission("execute", $this->getExecutePermission());
			$switches = $this->initializeSwitchUseCases();
			if(!empty($switches)) {
				$this->switchUseCases = $switches;
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function initializeSwitchUseCases(): ?array{
		return null;
	}

	public static function getJavaScriptClassPath(): ?string{
		$fn = get_class_filename(UseCase::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function setDataOperandObject($obj){
		$f = __METHOD__;
		if(!is_object($obj)) {
			Debug::error("{$f} input parameter is not an object");
		}
		$obj->setOperandFlag(true);
		return $this->dataOperandObject = $obj;
	}

	/**
	 *
	 * @return DataStructure
	 */
	public function getDataOperandObject(): ?DataStructure{
		$f = __METHOD__;
		try{
			if(!$this->hasDataOperandObject()) {
				if($this->hasPredecessor()) {
					$predecessor = $this->getPredecessor();
					if($predecessor instanceof InteractiveUseCase && $predecessor->hasDataOperandObject()) {
						return $this->setDataOperandObject($predecessor->getDataOperandObject());
					}
				}
				Debug::error("{$f} processed data object is undefined");
			}
			return $this->dataOperandObject;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getDataOperandSerialNumber(){
		return $this->getDataOperandObject()->getSerialNumber();
	}

	public function getDataOperandKey(){
		return $this->getDataOperandObject()->getIdentifierValue();
	}

	public function hasDataOperandObject(){
		return isset($this->dataOperandObject);
	}

	protected function getTransitionFromPermission(){
		return FAILURE;
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array{
		return $user->getStaticRoles();
	}

	public function getExecutePermission(){
		$f = __METHOD__;
		if($this->hasPermission("execute")) {
			return $this->getPermission("execute");
		}
		$epc = $this->getExecutePermissionClass();
		if(is_int($epc)) {
			return $epc;
		}elseif(is_bool($epc)) {
			if($epc) {
				return SUCCESS;
			}
			return FAILURE;
		}elseif(! class_exists($epc)) {
			Debug::error("{$f} class \"{$epc}\" does not exist");
		}
		return new $epc("execute");
	}

	public function acquireCorrespondentObject(mysqli $mysqli): ?UserData{
		$gcuc = new GetCorrespondentUseCase($this);
		$gcuc->execute();
		$gcuc->dispose();
		app()->setUseCase($this);
		return user()->hasCorrespondentObject() ? user()->getCorrespondentObject() : null;
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"reportTags",
			"skipAsyncRequestMethodConfig"
		]);
	}

	public function getReportTagsFlag(){
		return $this->getFlag("reportTags");
	}

	public function setReportTagsFlag($flag){
		$f = __METHOD__;
		if($flag) {
			// Debug::print("{$f} yes, we'll be reporting tags for this use case");
		}else{
			Debug::error("{$f} no, we won't be reporting tags for this use case");
		}
		return $this->setFlag("reportTags", $flag);
	}

	/**
	 *
	 * @return AccessAttempt
	 */
	public static function getBruteforceAttemptClass(): ?string{
		return static::getLoginAttemptClass();
	}

	public function setPredecessor(UseCase $predecessor): ?UseCase{
		$f = __METHOD__;
		try{
			$this->predecessor = $predecessor;
			if($predecessor->hasLoadoutGenerator()) {
				$this->setLoadoutGenerator($predecessor->getLoadoutGenerator());
			}
			return $predecessor;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasPredecessor(): bool{
		return isset($this->predecessor) && $this->predecessor instanceof UseCase;
	}

	public function getPredecessor(): ?UseCase{
		$f = __METHOD__;
		if(!$this->hasPredecessor()) {
			Debug::error("{$f} predecessor is undefined");
		}
		return $this->predecessor;
	}

	public static function getAdministratorClass(): string{
		return config()->getAdministratorClass();
	}

	public static function getNormalUserClass(): string{
		return config()->getNormalUserClass();
	}

	public function getPageContent(): ?array{
		$status = $this->getObjectStatus();
		return [
			ErrorMessage::getVisualError($status)
		];
	}

	protected function getHTMLElementClass(): string{
		return config()->getHTMLElementClass();
	}

	public function echoResponse(): void{
		$f = __METHOD__;
		try{
			$print = false;
			//Debug::checkMemoryUsage("Before creating HTML document");
			Debug::resetElementConstructorCount();

			if(ULTRA_LAZY) {
				$mode = ALLOCATION_MODE_ULTRA_LAZY;
			}else{
				$mode = ALLOCATION_MODE_LAZY;
			}
			$use_case = $this->getPageContentGenerator();
			$html_class = $this->getHTMLElementClass();
			$document = new $html_class($mode, $use_case);
			//app()->setDocumentRoot($document);
			if($print) {
				$mem2 = memory_get_usage();
				Debug::print("{$f} memory usage after HTMLElement binding and before echoElement: {$mem2}");
			}
			//Debug::checkMemoryUsage("Before echoing element");
			gc_enable();
			$document->echo(true);
			if($print) {
				$mem3 = memory_get_usage();
				Debug::print("{$f} memory usage after echoElement: {$mem3}");
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected static function getResponseClass(?UseCase $use_case = null): string{
		ErrorMessage::deprecated(__METHOD__);
	}

	public function getPageContentGenerator(): UseCase{
		return $this;
	}

	public function getUseCase(){
		return $this;
	}

	public static function hasMenu(): bool{
		return true;
	}

	public final function getChildSelectionParameters(DataStructure $parent, string $phylum, string $child_class){
		ErrorMessage::deprecated(__METHOD__);
	}

	public function generateChoices($context): ?array{
		return config()->generateChoices($context);
	}

	public function getLoadoutGeneratorClass(?PlayableUser $object=null):?string{
		if($this->hasPredecessor()) {
			return $this->getPredecessor()->getLoadoutGeneratorClass($object);
		}
		return config()->getLoadoutGeneratorClass($object);
	}
	
	public function getMenuElementClass(): ?string{
		return config()->getMenuElementClass();
	}

	public function beforeLoadHook(mysqli $mysqli): int{
		$this->dispatchEvent(new BeforeLoadEvent());
		return SUCCESS;
	}

	public function getTrimmableColumnNames(DataStructure $obj): ?array{
		return config()->getTrimmableColumnNames($obj);
	}

	public function afterLoadHook(mysqli $mysqli): int{
		$this->dispatchEvent(new AfterLoadEvent());
		return SUCCESS;
	}

	public function beforeAuthenticateHook(): int{
		$this->dispatchEvent(new BeforeAuthenticateEvent());
		return SUCCESS;
	}

	public function afterAuthenticateHook(): int{
		$this->dispatchEvent(new AfterAuthenticateEvent());
		return SUCCESS;
	}

	public function setBruteforceAttemptObject($attempt){
		return $this->bruteforceAttemptObject = $attempt;
	}

	public function execute(): int{
		$f = __METHOD__;
		return $this->setObjectStatus(SUCCESS);
	}

	public function hasSwitchUseCase(int $status): bool{
		$f = __METHOD__;
		$print = false;
		if($print) {
			if(!empty($this->switchUseCases) && array_key_exists($status, $this->switchUseCases)) {
				Debug::print("{$f} yes, there is a switch use case for status {$status}");
			}
		}
		switch ($status) {
			case ERROR_BAD_REQUEST:
			case ERROR_UNAUTHORIZED:
			case ERROR_PAYMENT_REQUIRED:
			case ERROR_FORBIDDEN:
			case ERROR_FILE_NOT_FOUND:
			case ERROR_METHOD_NOT_ALLOWED:
			case ERROR_NOT_ACCEPTABLE:
			case ERROR_PROXY_AUTHENTICATION:
			case ERROR_REQUEST_TIMEOUT:
			case ERROR_CONFLICT:
			case ERROR_GONE:
			case ERROR_LENGTH_REQUIRED:
			case ERROR_PRECONDITION_FAILED:
			case ERROR_PAYLOAD_TOO_LARGE:
			case ERROR_URI_TOO_LONG:
			case ERROR_UNSUPPORTED_MEDIA_TYPE:
			case ERROR_RANGE_NOT_SATISFIABLE:
			case ERROR_EXPECTATION_FAILED:
			case ERROR_MISDIRECTED_REQUEST:
			case ERROR_UNPROCESSABLE_ENTITY:
			case ERROR_LOCKED:
			case ERROR_FAILED_DEPENDENCY:
			case ERROR_TOO_EARLY:
			case ERROR_UPGRADE_REQUIRED:
			case ERROR_PRECONDITION_REQUIRED:
			case ERROR_TOO_MANY_REQUESTS:
			case ERROR_REQUEST_HEADER_FIELDS_TOO_LARGE:
			case ERROR_UNAVAILABLE_LEGAL:
			case ERROR_INTERNAL:
			case ERROR_NOT_IMPLEMENTED:
			case ERROR_BAD_GATEWAY:
			case ERROR_SERVICE_UNAVAILABLE:
			case ERROR_GATEWAY_TIMEOUT:
			case ERROR_HTTP_VERSION_UNSUPPORTED:
			case ERROR_VARIANT_ALSO_NEGOTIATES:
			case ERROR_INSUFFICIENT_STORAGE:
			case ERROR_LOOP_DETECTED:
			case ERROR_NOT_EXTENDED:
			case ERROR_NETWORK_AUTHENTICATION_REQUIRED:
				return true;
			default:
		}
		return ! empty($this->switchUseCases) && array_key_exists($status, $this->switchUseCases);
	}

	public function getSwitchUseCase(int $status){
		$f = __METHOD__;
		if(!$this->hasSwitchUseCase($status)) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::error("{$f} no switch use case for status \"{$err}\"");
		}

		switch ($status) {
			case ERROR_FILE_NOT_FOUND:
				return FileNotFoundUseCase::class;
			case ERROR_INTERNAL:
				return InternalServerErrorUseCase::class;
			case ERROR_BAD_REQUEST:
			case ERROR_UNAUTHORIZED:
			case ERROR_PAYMENT_REQUIRED:
			case ERROR_FORBIDDEN:
			case ERROR_METHOD_NOT_ALLOWED:
			case ERROR_NOT_ACCEPTABLE:
			case ERROR_PROXY_AUTHENTICATION:
			case ERROR_REQUEST_TIMEOUT:
			case ERROR_CONFLICT:
			case ERROR_GONE:
			case ERROR_LENGTH_REQUIRED:
			case ERROR_PRECONDITION_FAILED:
			case ERROR_PAYLOAD_TOO_LARGE:
			case ERROR_URI_TOO_LONG:
			case ERROR_UNSUPPORTED_MEDIA_TYPE:
			case ERROR_RANGE_NOT_SATISFIABLE:
			case ERROR_EXPECTATION_FAILED:
			case ERROR_MISDIRECTED_REQUEST:
			case ERROR_UNPROCESSABLE_ENTITY:
			case ERROR_LOCKED:
			case ERROR_FAILED_DEPENDENCY:
			case ERROR_TOO_EARLY:
			case ERROR_UPGRADE_REQUIRED:
			case ERROR_PRECONDITION_REQUIRED:
			case ERROR_TOO_MANY_REQUESTS:
			case ERROR_REQUEST_HEADER_FIELDS_TOO_LARGE:
			case ERROR_UNAVAILABLE_LEGAL:
			case ERROR_NOT_IMPLEMENTED:
			case ERROR_BAD_GATEWAY:
			case ERROR_SERVICE_UNAVAILABLE:
			case ERROR_GATEWAY_TIMEOUT:
			case ERROR_HTTP_VERSION_UNSUPPORTED:
			case ERROR_VARIANT_ALSO_NEGOTIATES:
			case ERROR_INSUFFICIENT_STORAGE:
			case ERROR_LOOP_DETECTED:
			case ERROR_NOT_EXTENDED:
			case ERROR_NETWORK_AUTHENTICATION_REQUIRED:
				$uc = new StatusCodeUseCase($this);
				$uc->setObjectStatus($status);
				return $uc;
			default:
		}
		return $this->switchUseCases[$status];
	}

	public static function skipThemes(): bool{
		return false;
	}

	public function sendHeaders(Request $request): bool{
		$f = __METHOD__;
		try{
			$ts = time();
			$print = false;
			session_start();
			// detect session hijack attempts and restart expired sessions
			$hijacked = false;
			$status = AntiHijackSessionData::protect();
			if($status !== SUCCESS) {
				Debug::warning("{$f} possible session hijacking attempt detected");
				if(! session_regenerate_id(false)) {
					Debug::error("{$f} error regenerating session ID");
				}
				$hijacked = true;
			}elseif(isset($_SESSION['lastActiveTimestamp'])) {
				if($print) {
					Debug::print("{$f} last activity timestamp is defined");
				}
				if(($ts - $_SESSION['lastActiveTimestamp']) >= intval(ini_get("session.gc_maxlifetime"))) {
					if($print) {
						Debug::print("{$f} session has expired");
					}
					session_unset();
					session_destroy();
				}elseif($print) {
					Debug::print("{$f} session is still fresh");
				}
			}elseif($print) {
				Debug::print("{$f} last activity timestamp is undefined");
			}
			// regenerate expired session timestamp
			if($hijacked || ! isset($_SESSION['regenerationTimestamp'])) {
				if($print) {
					Debug::print("{$f} session was possibly hijacked or regeneration timestamp is undefined, resetting it now");
				}
				$_SESSION['regenerationTimestamp'] = $ts;
			}elseif(($ts - $_SESSION['regenerationTimestamp'] >= SESSION_REGENERATION_INTERVAL)) {
				if($print) {
					$old_session_id = session_id();
					Debug::print("{$f} session \"{$old_session_id}\" is due for regeneration");
				}
				if(! session_regenerate_id(true)) {
					Debug::error("{$f} error regenerating session ID");
				}
				if($print) {
					$new_session_id = session_id();
					Debug::print("{$f} new session ID is \"{$new_session_id}\"");
				}
				$_SESSION['regenerationTimestamp'] = $ts;
			}elseif($print) {
				Debug::print("{$f} regeneration timestamp is defined and sufficiently fresh");
			}
			$_SESSION['lastActiveTimestamp'] = $ts;
			AntiXsrfTokenData::initializeSessionToken(1);
			AntiXsrfTokenData::initializeSessionToken(2);
			$session = new AntiXsrfTokenData();
			if(!$session->hasAntiXsrfToken()) {
				Debug::error("{$f} session is uninitialized");
			}elseif($print) {
				Debug::print("{$f} anti-XSRF token is initialized");
			}
			return $hijacked;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function getValidCrossOriginHosts(): ?array{
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::print("{$f} if you want to enable CORS for a specific use case, redeclare this function in a derived class");
		}
		return [];
	}

	public function validateCrossOriginRequest(array $parsed_origin){
		$f = __METHOD__;
		try{
			$print = false;
			$hosts = $this->getValidCrossOriginHosts();
			if(! array_key_exists('host', $parsed_origin) || empty($hosts) || false === array_search($parsed_origin['host'], $hosts)) {
				Debug::warning("{$f} failed cross origin request validation");
				return FAILURE;
			}elseif($print) {
				Debug::print("{$f} cross origin request validated");
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function debugUseCase(): bool{
		return false;
	}

	public static function allowFileUpload(): bool{
		return false;
	}

	public function blockCrossOriginRequest(){
		$f = __METHOD__;
		Debug::warning("{$f} cross origin request blocked");
		$use_case = new StatusCodeUseCase($this);
		$use_case->setObjectStatus(ERROR_CROSS_ORIGIN_REQUEST);
		app()->setUseCase($use_case);
		$use_case->execute(false);
		$this->echoResponse();
	}

	public static function isSiteMappable():bool{
		return false;
	}

	/**
	 * validates that the use case is permitted to continue the execution chain handed off to it by its predecessor
	 *
	 * @return string
	 */
	public function validateTransition(): int{
		$f = __METHOD__;
		try{
			if(!$this->hasPredecessor()) {
				Debug::warning("{$f} predecessor is undefined");
				return $this->setObjectStatus(ERROR_NULL_PREDECESSOR);
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function getTransitionToPermissionClass(){
		return SUCCESS;
	}

	public function getTransitionToPermission(){
		$ttp = $this->getTransitionToPermissionClass();
		if(is_int($ttp)) {
			return $ttp;
		}elseif(is_bool($ttp)) {
			if($ttp) {
				return SUCCESS;
			}
			return FAILURE;
		}
		return new $ttp("transitionTo");
	}

	protected function beforeExecuteHook(): int{
		$this->dispatchEvent(new BeforeExecuteEvent());
		return SUCCESS;
	}

	public function beforeTransitionHook(UseCase $successor): int{
		$this->dispatchEvent(new UseCaseTransitionEvent($successor));
		return true;
	}

	protected function afterExecuteHook($status): int{
		$this->dispatchEvent(new AfterExecuteEvent($status));
		return SUCCESS;
	}

	public function reconfigureDataStructure(DataStructure $ds): int{
		return SUCCESS;
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		if($status === SUCCESS && request()->getProgressiveHyperlinkFlag()) {
			if($print) {
				Debug::print("{$f} returning ProgressiveHyperlinkResponder");
			}
			return new ProgressiveHyperlinkResponder();
		}elseif(hasInputParameter('refresh_session')) {
			if($print) {
				Debug::print("{$f} refreshing session");
			}
			return new RefreshSessionTimeoutResponder();
		}elseif($print) {
			Debug::print("{$f} nothing to do here");
		}
		return new Responder();
	}

	public function safeExecute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			$user = user();
			$status = $this->permit($user, "execute");
			if($status === SUCCESS) {
				$status = $this->beforeExecuteHook();
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} beforeExecuteHook returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}else{
					$result = $this->execute();
					if(!is_int($result)) {
						Debug::error("{$f} result is not an integer");
					}
					$this->setObjectStatus($result);
					if($print && $status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} execute returned error status \"{$err}\"");
					}
					$status = $this->afterExecuteHook($status);
					if($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} afterExecuteHook returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} after execute hook successful");
					}
				}
			}else{
				if($print) {
					Debug::error("{$f} user was denied the execute permission");
				}
				$this->setPermission("execute", ERROR_FORBIDDEN);
				return $this->setObjectStatus($status);
			}
			return $this->setObjectStatus($result);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getDefaultWorkflowClass(): string{
		return config()->getDefaultWorkflowClass();
	}
	
	public function hasLoadoutGenerator():bool{
		return isset($this->loadoutGenerator) && $this->loadoutGenerator instanceof LoadoutGenerator;
	}
	
	public function getLoadoutGenerator(?PlayableUser $user=null):?LoadoutGenerator{
		$f = __METHOD__;
		$print = false;
		if($this->hasLoadoutGenerator()){
			if($print){
				Debug::print("{$f} loadout was already generated");
			}
			return $this->loadoutGenerator;
		}
		$lgc = $this->getLoadoutGeneratorClass($user);
		if($lgc){
			if($print){
				Debug::print("{$f} returning a new {$lgc}");
			}
			return $this->setLoadoutGenerator(new $lgc());
		}elseif($print){
			Debug::print("{$f} returning null");
		}
		return null;
	}
	
	public function setLoadoutGenerator(?LoadoutGenerator $lg):?LoadoutGenerator{
		if($lg === null){
			unset($this->loadoutGenerator);
			return null;
		}
		$this->loadoutGenerator = $lg;
		if($this->hasPredecessor()){
			return $this->getPredecessor()->setLoadoutGenerator($lg);
		}
		return $lg;
	}
	
	public function getLoadEntryPointUseCase():UseCase{
		if($this->hasPredecessor()){
			return $this->getPredecessor();
		}
		return $this;
	}
}
