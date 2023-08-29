<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ClassifyIpAddressResponder extends Responder{
	
	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case):void{
		//parent::modifyResponse($response, $use_case);
		$status = $use_case->getObjectStatus();
		$updated_element = ErrorMessage::getVisualError($status);
		$updated_element->setIdAttribute("confirm_ip_list_form");
		$command = new UpdateElementCommand($updated_element);
		$response->pushCommand($command);
	}
}
