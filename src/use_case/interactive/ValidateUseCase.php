<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;

class ValidateUseCase extends SubsequentUseCase{

	public function execute(): int{
		$f = __METHOD__;
		$print = false;
		$validator = $this->getPredecessor()->getValidator();
		$status = $validator->getObjectStatus();
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} validator has error status \"{$err}\"");
			return $this->setObjectStatus($status);
		} elseif ($print) {
			Debug::print("{$f} validation successful");
		}
		return $this->setObjectStatus($status);
	}
}
