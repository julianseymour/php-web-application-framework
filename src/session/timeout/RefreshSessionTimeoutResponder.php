<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\timeout;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class RefreshSessionTimeoutResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$response->pushCommand(new ResetSessionTimeoutCommand());
	}
}