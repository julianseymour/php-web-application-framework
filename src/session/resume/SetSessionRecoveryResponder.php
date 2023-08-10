<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class SetSessionRecoveryResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$form = new SessionRecoverySettingsForm(ALLOCATION_MODE_LAZY, new SessionRecoveryData());
		$response->pushCommand($form->update(), CommandBuilder::infoBox(Document::createElement("div")->withInnerHTML(ErrorMessage::getResultMessage(RESULT_DELETE_SESSIONS_SUCCESS))));
	}
}
