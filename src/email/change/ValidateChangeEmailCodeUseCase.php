<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidateAuthenticatedConfirmationCodeUseCase;

class ValidateChangeEmailCodeUseCase extends ValidateAuthenticatedConfirmationCodeUseCase
{

	public static function getBruteforceAttemptClass(): string
	{
		return ConfirmEmailAttempt::class;
	}

	public static function getConfirmationCodeClass(): string
	{
		return ChangeEmailAddressConfirmationCode::class;
	}

	public function getUseCaseId()
	{
		return USE_CASE_EMAIL_CHANGE_CONFIRM;
	}

	public function getActionAttribute(): ?string
	{
		return "/confirm_email";
	}

	public function getFormClass(): ?string
	{
		return null;
	}

	public static function validateOnFormSubmission(): bool
	{
		return false;
	}

	protected function initializeSwitchUseCases(): ?array
	{
		return [
			SUCCESS => ChangeEmailAddressUseCase::class
		];
	}

	protected function getExecutePermissionClass()
	{
		return AuthenticatedAccountTypePermission::class;
	}
}