<?php

namespace JulianSeymour\PHPWebApplicationFramework\poll;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ShortPollResponder extends Responder{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case): void{
		$f = __METHOD__;
		$print = false;
		parent::modifyResponse($response, $use_case);
		$use_cases = $use_case->getUseCases();
		$count = count($use_cases);
		if ($count == 0) {
			Debug::error("{$f} not polling use cases");
		}
		foreach ($use_cases as $poller) {
			if ($print) {
				$ucc = get_class($poller);
			}
			if ($poller->getDisabledFlag()) {
				if ($print) {
					Debug::print("{$f} {$ucc} is disabled");
				}
				continue;
			} elseif ($print) {
				Debug::print("{$f} about to get responder for {$ucc}");
			}
			$status = $use_case->getObjectStatus();
			$responder = $poller->getResponder($status);
			if ($print) {
				$rc = get_class($responder);
				Debug::print("{$f} about to push a {$rc}");
			}
			$responder->modifyResponse($response, $poller);
		}
		if ($print) {
			Debug::print("{$f} returning");
		}
	}
}