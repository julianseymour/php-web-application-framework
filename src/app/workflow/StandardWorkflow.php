<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\workflow;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticateUseCase;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadTreeUseCase;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\session\hijack\SessionHijackWarningUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class StandardWorkflow extends Workflow{

	public function handleRequest(Request $request, UseCase $entry_point): int{
		$f = __METHOD__;
		try {
			$print = false;
			$random = sha1(random_bytes(32));
			app()->setUseCase($entry_point);
			// set XHR and service worker POST flags if this is a fetch event
			Request::configureAsynchronousRequestMethodFlags();
			// send headers
			$filename = "";
			$line = 0;
			if (headers_sent($filename, $line)) {
				Debug::error("{$f} headers already sent in {$filename}:{$line}");
			}
			$hijacked = $entry_point->sendHeaders($request);
			if ($hijacked) {
				return $this->execute($request, new SessionHijackWarningUseCase($this));
			}
			app()->advanceExecutionState(EXECUTION_STATE_HEADERS_SENT);
			// authentifcation
			if ($print) {
				Debug::print("{$f} about to authenticate");
			}
			$status = $this->authenticate($request, $entry_point);
			// return early if execution state has already been advanced
			if (app()->getExecutionState() >= EXECUTION_STATE_EXECUTED) {
				if ($print) {
					Debug::print("{$f} already authenticated. random string is \"{$random}\"");
				}
				return SUCCESS;
			} elseif ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} authenticate returned error status \"{$err}\"; random string is \"{$random}\"");
				$entry_point->setObjectStatus($status);
				return $this->respond($entry_point);
			} elseif ($print) {
				Debug::print("{$f} authentication successful. random string is \"{$random}\"");
			}
			// load whatever is needed for this use case from the database
			$load_tree = new LoadTreeUseCase($entry_point);
			$status = $load_tree->execute();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} load tree use case returned error status \"{$err}\". Random string is \"{$random}\"");
				$entry_point->setObjectStatus($status);
				return $this->respond($entry_point);
			}
			// preprocess incoming files
			if (! $entry_point->allowFileUpload()) {
				if ($print) {
					Debug::print("{$f} file upload is not allowed by this use case");
				}
			} elseif (! $request instanceof Request) {
				if ($print) {
					Debug::print("{$f} received an input parameter that is not a Request");
				}
			} elseif (empty($_FILES)) {
				if ($print) {
					Debug::print("{$f} FILES superglobal is empty");
				}
			} else {
				// XXX make sure the user has the upload permission for the current use case
				if ($print) {
					Debug::print("{$f} use case allows file upload, request is a Request, and the FILES superglobal is not empty");
				}
				$files = $request->repackIncomingFiles($_FILES);
				if (! empty($files)) {
					if ($print) {
						Debug::print("{$f} about to set repacked incoming files");
					}
					$request->setRepackedIncomingFiles($files);
				} elseif ($print) {
					Debug::print("{$f} repacked incoming files array is empty");
				}
			}
			if ($print) {
				Debug::print("{$f} about to execute entry point of class " . $entry_point->getClass() . ". rando mstring is \"{$random}\"");
			}
			return $this->execute($request, $entry_point);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function execute(Request $request, UseCase $entry_point): int{
		$f = __METHOD__;
		try {
			$print = false;
			$use_case = $entry_point;
			$status = FAILURE;
			$iterations = 0;
			while (true) {
				app()->setUseCase($use_case);
				$ucc = $use_case->getClass();
				if ($print) {
					++ $iterations;
					Debug::print("{$f} about to execute {$ucc} on iteration #{$iterations}");
				}
				$status = $use_case->safeExecute();
				if(!is_int($status)){
					$gottype = is_object($status) ? get_short_class($status) : gettype($status);
					Debug::error("{$f} {$ucc}->execute() returned a(n) {$gottype}");
				}
				$use_case->setObjectStatus($status);
				if ((Request::isXHREvent() || Request::isFetchEvent()) && ! $use_case->getDisabledFlag()) {
					// Debug::checkMemoryUsage("Before Responder->modifyResponse()");
					$responder = $use_case->getResponder($status);
					if ($responder instanceof Responder) {
						if ($print) {
							$rc = get_class($responder);
							$decl = $responder->getDeclarationLine();
							Debug::print("{$f} use case \"{$ucc}\" returned responder of class \"{$rc}\", instantiated on {$decl}");
						}
						$responder->modifyResponse(app()->getResponse(), $use_case);
					} elseif ($print) {
						Debug::print("{$f} no responder for use case \"{$ucc}\"");
					}
				} elseif ($print) {
					Debug::print("{$f} the use case is not disabled, and this is not an XHR or fetch event");
				}
				// transfer control to the next use case
				if (! $use_case->hasSwitchUseCase($status)) {
					if ($print) {
						$ucc = $use_case->getClass();
						$err = ErrorMessage::getResultMessage($status);
						Debug::print("{$f} {$ucc} has no switch for status {$status} ({$err})");
					}
					break;
				}
				$class = $use_case->getSwitchUseCase($status);
				if ($print) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} {$ucc} transitions to ".get_short_class($class)." on status code {$status} ({$err})");
				}
				$use_case->setPermission("transitionTo", $use_case->getTransitionToPermission());
				$status = $use_case->permit(user(), "transitionTo", $class);
				if ($status !== SUCCESS) {
					if ($print) {
						Debug::print("{$f} permission denied for transitioning to next use case for status \"{$status}\"");
					}
					$use_case->setObjectStatus($status);
					break;
				} elseif ($print) {
					Debug::print("{$f} transition authorized");
				}
				if ($class instanceof UseCase) {
					// Debug::warning("{$f} untested: switching directly to a pre-instantiated use case");
					$switch = $class;
					$switch->setPredecessor($use_case);
				} elseif (is_string($class) && class_exists($class) && is_a($class, UseCase::class, true)) {
					$switch = new $class($use_case);
				} else {
					Debug::error("{$f} neither of the above");
				}
				// $status = $switch->getObjectStatus();
				if ($print) {
					// $err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} handing control over from ".$use_case->getShortClass()." to ".$switch->getShortClass()." for error code {$status} (\"{$err}\")");
				}
				$status = $use_case->beforeTransitionHook($switch);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} beforeTransitionHook returned error status \"{$err}\"");
				}
				$user = app()->hasUserData() ? app()->getUserData() : null;
				$status = $switch->permit($user, DIRECTIVE_TRANSITION_FROM, $use_case);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} successor of class ".get_short_class($switch)." transition from permission status \"{$err}\"");
					break;
				}
				if ($print) {
					Debug::print("{$f} successor use case transition from permission granted");
				}
				$use_case = $switch;
			}
			app()->advanceExecutionState(EXECUTION_STATE_EXECUTED);
			return $this->respond($use_case);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function authenticate(Request $request, UseCase $entry_point): int{
		$f = __METHOD__;
		try {
			$print = false;
			// before authenticate hook
			$status = $entry_point->beforeAuthenticateHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeAuthenticateHook returned error status \"{$err}\"");
				$entry_point->setObjectStatus($status);
				return $this->execute($request, $entry_point);
			}
			if (app()->getExecutionState() >= EXECUTION_STATE_AUTHENTICATED) {
				if ($print) {
					Debug::print("{$f} already authenticated");
				}
				return SUCCESS;
			} elseif ($print) {
				Debug::print("{$f} did not switch use cases inside this function. About to authenticate.");
			}
			// authenticate
			$auth = new AuthenticateUseCase($entry_point);
			$auth->execute();
			$auth->dispose();
			app()->setUseCase($entry_point);
			// after authenticate hook
			$status = $entry_point->afterAuthenticateHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterAuthenticateHook returned error status \"{$err}\"");
				return $entry_point->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
