<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\dismiss;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\element\DeleteElementCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class DismissNotificationResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case): void
	{
		parent::modifyResponse($response, $use_case);
		if ($use_case->getObjectStatus() === SUCCESS) { // Request::isXHREvent()){
			$do = $use_case->getDataOperandObject();
			$iec = $do->getElementClass();
			$deleted_element = new $iec(ALLOCATION_MODE_NEVER);
			$deleted_element->bindContext($do);
			$response->pushCommand(new DeleteElementCommand($deleted_element));
		}
	}
}