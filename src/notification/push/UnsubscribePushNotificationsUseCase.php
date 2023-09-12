<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class UnsubscribePushNotificationsUseCase extends UseCase{

	public function execute(): int{
		$f = __METHOD__;
		try{
			Debug::error(ErrorMessage::getResultMessage(ERROR_NOT_IMPLEMENTED));
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/unsubscribe";
	}
}
