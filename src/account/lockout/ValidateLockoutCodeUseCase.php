<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\lockout;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidateAnonymousConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

class ValidateLockoutCodeUseCase extends ValidateAnonymousConfirmationCodeUseCase{

	public static function getFormDisplayStatus(){
		return ERROR_DISPATCH_NOTHING;
	}

	public static function getBruteforceAttemptClass(): string{
		return LockoutWaiverAttempt::class;
	}

	public function afterLoadHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$status = parent::afterLoadHook($mysqli);
			$user = user();
			if (! $user instanceof AnonymousUser) {
				Debug::warning("{$f} user is already logged in");
				return $this->setObjectStatus(ERROR_ALREADY_LOGGED);
			} elseif (! $user->isEnabled()) {
				Debug::warning("{$f} user is not enabled");
				return $this->setObjectStatus(ERROR_ACCOUNT_DISABLED);
			}
			Debug::print("{$f} user is not already logged in");
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function getExecutePermissionClass(){
		return AnonymousAccountTypePermission::class;
	}

	public static function getConfirmationCodeClass(): string{
		return LockoutConfirmationCode::class;
	}

	public function getActionAttribute(): ?string{
		return "/unlock";
	}

	public function getFormClass(): ?string{
		return null;
	}

	public static function validateOnFormSubmission(): bool
	{
		return false;
	}

	protected function initializeSwitchUseCases(): ?array
	{
		return [
			SUCCESS => WaiveLockoutUseCase::class
		];
	}

	public function beforeTransitionHook(UseCase $successor): int{
		parent::beforeTransitionHook($successor);
		if (! $successor instanceof WaiveLockoutUseCase) {
			return FAILURE;
		} elseif ($this->getObjectStatus() === SUCCESS) {
			$successor->setObjectStatus(RESULT_BFP_WAIVER_SUCCESS);
		}
		return SUCCESS;
	}
}
