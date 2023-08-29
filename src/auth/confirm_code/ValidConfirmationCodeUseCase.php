<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

abstract class ValidConfirmationCodeUseCase extends SubsequentUseCase{

	public function validateTransition(): int{
		$f = __METHOD__;
		try {
			$print = false;
			$status = parent::validateTransition();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} parent function successful");
			}
			$predecessor = $this->getPredecessor();
			if (! $predecessor instanceof ValidateConfirmationCodeUseCase) {
				$pc = $predecessor->getClass();
				Debug::error("{$f} predecessor has invalid class \"{$pc}\"");
				return $this->setObjectStatus(ERROR_PREDECESSOR_CLASS);
			}elseif($print){
				Debug::print("{$f} predecessor is a validate confirmation code use case");
			}
			$status = $predecessor->getObjectStatus();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} predecessor has error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} predecessor executed successfully");
			}
			$confirmation_code = $predecessor->getConfirmationCodeObject();
			$status = $confirmation_code->getObjectStatus();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} confirmation code has error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully validated transition");
			}
			$this->transitionValidated = true;
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
	
	protected function getTransitionFromPermission(){
		$f = __METHOD__;
		$print = false;
		return new Permission("transitionFrom", function(PlayableUser $user, ValidConfirmationCodeUseCase $subject, UseCase $predecessor) use ($f, $print){
			if($predecessor instanceof ValidateConfirmationCodeUseCase){
				if($print){
					Debug::print("{$f} predecessor is satisfactory");
				}
				$subject->validateTransition();
				return SUCCESS;
			}elseif($print){
				Debug::print("{$f} predecessor class is ".$predecessor->getShortClass());
			}
			return FAILURE;
		});
	}
}
