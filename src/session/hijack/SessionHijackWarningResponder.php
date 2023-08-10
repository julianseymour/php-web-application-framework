<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\hijack;

use JulianSeymour\PHPWebApplicationFramework\account\logout\LogoutResponder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class SessionHijackWarningResponder extends LogoutResponder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$response->pushCommand(new InfoBoxCommand($use_case->getSessionHijackWarningElement()));
	}
}