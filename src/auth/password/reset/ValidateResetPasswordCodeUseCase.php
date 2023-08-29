<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidateAnonymousConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class ValidateResetPasswordCodeUseCase extends ValidateAnonymousConfirmationCodeUseCase{

	public static function getBruteforceAttemptClass(): string{
		return ResetPasswordAttempt::class;
	}

	public function getDataOperandObject(): ?DataStructure{
		$user_class = mods()->getUserClass(NormalUser::getAccountTypeStatic());
		$user = new $user_class();
		return $user;
	}

	public static function getConfirmationCodeClass(): string{
		return ResetPasswordConfirmationCode::class;
	}

	public function getActionAttribute(): ?string{
		return "/reset";
	}

	public static function validateOnFormSubmission(): bool{
		return true;
	}

	public function getFormClass(): ?string{
		return ResetPasswordForm::class;
	}

	protected function initializeSwitchUseCases(): ?array{
		return [
			SUCCESS => ResetPasswordUseCase::class
		];
	}

	protected function getExecutePermissionClass(){
		return AnonymousAccountTypePermission::class;
	}
}
