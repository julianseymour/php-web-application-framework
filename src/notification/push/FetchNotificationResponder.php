<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class FetchNotificationResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$note = $use_case->getDataOperandObject();
		$note->configureArrayMembership("push");
		$response->pushDataStructure($note);
	}
}
