<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class AjaxValidatorResponder extends Responder{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		$print = false;
		parent::modifyResponse($response, $use_case);
		$validator = $use_case->getValidator();
		$status = $use_case->getObjectStatus();
		if($status !== SUCCESS) {
			if($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} validation returned error status \"{$err}\"");
			}
			$response->pushCommand($validator->getFailureCommand());
		}else{
			if($print) {
				Debug::print("{$f} validation successful");
			}
			$response->pushCommand($validator->getSuccessCommand());
		}
	}
}
