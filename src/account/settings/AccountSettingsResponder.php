<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\settings;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\input\CheckInputCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\UpdateResponder;

class AccountSettingsResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$check = new CheckInputCommand("radio_settings_none");
		$check->pushSubcommand(UpdateResponder::generateCommand($use_case));
		$response->pushCommand($check, CommandBuilder::infoBox(Document::createElement("div")->withInnerHTML(ErrorMessage::getResultMessage(RESULT_SETTINGS_UPDATED))));
	}
}
