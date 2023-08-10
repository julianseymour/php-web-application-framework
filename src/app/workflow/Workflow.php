<?php
namespace JulianSeymour\PHPWebApplicationFramework\app\workflow;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\event\AfterRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

abstract class Workflow extends Basic
{

	use EventListeningTrait;

	public abstract function handleRequest(Request $request, UseCase $entry_point);

	protected function beforeRespondHook(): int
	{
		$this->dispatchEvent(new BeforeRespondEvent());
		return SUCCESS;
	}

	public function respond(UseCase $use_case): int
	{
		$f = __METHOD__; //Workflow::getShortClass()."(".static::getShortClass().")->respond()";
		try {
			$print = false;
			$status = $this->beforeRespondHook();
			app()->advanceExecutionState(EXECUTION_STATE_RESPONDING);
			if (request()->isXHREvent() || request()->isCurlEvent() || request()->isFetchEvent()) {
				if ($print) {
					Debug::print("{$f} this is an XHR, fetch or curl request");
				}
				$response = app()->getResponse();
				if (! $response->getAllocatedFlag()) {
					Debug::error("{$f} response has already been echoed");
				}
				/*
				 * if($use_case instanceof ClientUseCaseInterface && !$response->hasProperty("action")){
				 * $action = $use_case->getClientUseCaseName();
				 * if($action !== null){
				 * $response->setProperty("action", $action);
				 * }
				 * }
				 */
				$response->echoJson(true);
				if ($print) {
					Debug::printStackTraceNoExit("{$f} echoed response");
				}
				app()->advanceExecutionState(EXECUTION_STATE_RESPONDED);
				app()->dispatchCallbacks();
				app()->advanceExecutionState(EXECUTION_STATE_TERMINATED);
				if (Debug::isTrapArmed()) {
					Debug::print("{$f} disarming trap");
					Debug::disarmTrap();
				}
				$status = $this->afterRespondHook();
				return $use_case->getObjectStatus();
			} elseif ($print) {
				$mem1 = memory_get_usage();
				Debug::print("{$f} memory usage before HTMLElement binding: {$mem1}");
			}
			$use_case->echoResponse();
			iF ($print) {
				Debug::print("{$f} echoed response");
			}
			app()->advanceExecutionState(EXECUTION_STATE_RESPONDED);
			app()->dispatchCallbacks();
			app()->advanceExecutionState(EXECUTION_STATE_TERMINATED);
			if (Debug::isTrapArmed()) {
				Debug::print("{$f} disarming trap");
				Debug::disarmTrap();
			}
			$status = $this->afterRespondHook();
			return $use_case->getObjectStatus();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterRespondHook(): int
	{
		$this->dispatchEvent(new AfterRespondEvent());
		return SUCCESS;
	}
}
