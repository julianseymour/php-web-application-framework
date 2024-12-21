<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\workflow;

use function JulianSeymour\PHPWebApplicationFramework\app;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class SimpleWorkflow extends Workflow{

	public function handleRequest(Request $request, UseCase $entry_point){
		app()->setUseCase($entry_point);
		$entry_point->sendHeaders($request);
		$entry_point->echoResponse();
	}
}