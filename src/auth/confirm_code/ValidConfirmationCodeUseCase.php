<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;

abstract class ValidConfirmationCodeUseCase extends SubsequentUseCase
{

	public function validateTransition(): int
	{
		$f = __METHOD__; //ValidConfirmationCodeUseCase::getShortClass()."(".static::getShortClass().")->validateTransition()";
		try {
			$status = parent::validateTransition();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$predecessor = $this->getPredecessor();
			if (! $predecessor instanceof ValidateConfirmationCodeUseCase) {
				$pc = $predecessor->getClass();
				Debug::error("{$f} predecessor has invalid class \"{$pc}\"");
				return $this->setObjectStatus(ERROR_PREDECESSOR_CLASS);
			}
			$status = $predecessor->getObjectStatus();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} predecessor has error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$confirmation_code = $predecessor->getConfirmationCodeObject();
			$status = $confirmation_code->getObjectStatus();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} confirmation code has error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			Debug::print("{$f} successfully validated transition");
			$this->transitionValidated = true;
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
