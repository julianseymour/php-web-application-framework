<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class RegistrationErrorResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$response->pushCommand(Document::createElement("div")->withIdAttribute("register_notice")
			->withInnerHTML(ErrorMessage::getResultMessage($use_case->getObjectStatus()))
			->update());
	}
}