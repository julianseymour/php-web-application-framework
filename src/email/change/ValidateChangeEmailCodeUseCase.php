<?php

namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\ExecutiveLoginWorkflow;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidateAuthenticatedConfirmationCodeUseCase;

class ValidateChangeEmailCodeUseCase extends ValidateAuthenticatedConfirmationCodeUseCase{

	public static function getBruteforceAttemptClass(): string{
		return ConfirmEmailAttempt::class;
	}

	public static function getConfirmationCodeClass(): string{
		return ChangeEmailAddressConfirmationCode::class;
	}

	public function getActionAttribute(): ?string{
		return "/confirm_email";
	}

	public function getFormClass(): ?string{
		return null;
	}

	public static function validateOnFormSubmission(): bool{
		return false;
	}

	protected function initializeSwitchUseCases(): ?array{
		return [
			SUCCESS => ChangeEmailAddressUseCase::class
		];
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public function beforeExecuteHook():int{
		$ret = parent::beforeExecuteHook();
		if(user() instanceof AnonymousUser){
			return ERROR_MUST_LOGIN;
		}
		return $ret;
	}
	
	public static function getDefaultWorkflowClass():string{
		return ExecutiveLoginWorkflow::class;
	}
}