<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\change;


use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\ui\FlipPanels;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ChangePasswordResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		$f = __METHOD__;
		parent::modifyResponse($response, $use_case);
		$user = user();
		if ($user instanceof AuthenticatedUser) {
			Debug::error("{$f} user data is not anonymous");
		}
		$updated_element = new DivElement(ALLOCATION_MODE_LAZY);
		$updated_element->setCatchReportedSubcommandsFlag(true);
		$user_cp = new FlipPanels(ALLOCATION_MODE_LAZY, $user);
		$updated_element->setIdAttribute("login_replace");
		$updated_element->appendChild($user_cp);
		$response->pushCommand(
			CommandBuilder::infoBox(Document::createElement("div")->withInnerHTML(ErrorMessage::getResultMessage(RESULT_CHANGEPASS_SUCCESS))), $updated_element->update());
	}
}
