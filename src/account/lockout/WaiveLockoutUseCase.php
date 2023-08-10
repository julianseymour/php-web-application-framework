<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\lockout;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

/**
 * account lockout has already been waived by writing the successful validation attempt
 * in ValidateLockoutConfirmationCode, so this class doesn't do anything
 *
 * @author j
 */
class WaiveLockoutUseCase extends ValidConfirmationCodeUseCase
{

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getUseCaseId()
	{
		return USE_CASE_LOCKOUT_WAIVER;
	}

	public function getActionAttribute(): ?string
	{
		return "/unlock";
	}

	public function execute(): int
	{
		$status = parent::execute();
		if ($status === SUCCESS) {
			return RESULT_BFP_WAIVER_SUCCESS;
		}
		return $status;
	}

	public function getTransitionFromPermission(): Permission
	{
		return new Permission(DIRECTIVE_TRANSITION_FROM, function (PlayableUser $user, UseCase $target, UseCase $predecessor) {
			$f = __METHOD__; //"WaiveLockoutUseCase transition from permission closure";
			try {
				$print = false;
				if (! $predecessor instanceof ValidateLockoutCodeUseCase) {
					if ($print) {
						Debug::print("{$f} predecessor is the wrong class");
					}
					return FAILURE;
				} elseif ($print) {
					Debug::print("{$f} predecessor has the right class");
				}
				$status = $predecessor->getObjectStatus();
				if ($status !== SUCCESS) {
					if ($print) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} predecessor has error status \"{$err}\"");
					}
					return $status;
				} elseif ($print) {
					Debug::print("{$f} transition validated");
				}
				return SUCCESS;
			} catch (Exception $x) {
				x($f, $x);
			}
		});
	}
}
