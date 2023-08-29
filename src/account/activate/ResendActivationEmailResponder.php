<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ResendActivationEmailResponder extends Responder{
	
	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case):void{
		$response->pushCommand(
			new InfoBoxCommand(
				substitute(
					_("A new email has been sent to %1% containing a link to activate your account."),
					user()->getEmailAddress()
				)
			)
		);
	}
}