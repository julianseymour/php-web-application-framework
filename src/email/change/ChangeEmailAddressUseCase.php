<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

class ChangeEmailAddressUseCase extends ValidConfirmationCodeUseCase
{

	public function execute(): int
	{
		$f = __METHOD__; //ChangeEmailAddressUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$confirmation_code = $this->getPredecessor()->getConfirmationCodeObject();
			$email = $confirmation_code->getNewEmailAddress();
			$user = user();
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$user->setNormalizedEmailAddress(EmailAddressDatum::normalize($email));
			$user->setEmailAddress($email);
			$status = $user->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} user->update() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return $this->setObjectStatus(RESULT_CHANGEMAIL_SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getActionAttribute(): ?string
	{
		return "/confirm_email";
	}

	public function getUseCaseId()
	{
		return USE_CASE_EMAIL_CHANGE_CONFIRM;
	}

	protected function getTransitionFromPermission()
	{
		$f = __METHOD__; //ChangeEmailAddressUseCase::getShortClass()."(".static::getShortClass().")->getTransitionFromPermission()";
		return new Permission("transitionFrom", function ($user, $use_case, $predecessor) use ($f) {
			if ($predecessor instanceof ValidateChangeEmailCodeUseCase && $user instanceof AuthenticatedUser && $predecessor->getObjectStatus() === SUCCESS) {
				Debug::print("{$f} permission granted");
				$use_case->validateTransition();
				return $this->setObjectStatus(SUCCESS);
			}
			Debug::print("{$f} permission denied");
			return $this->setObjectStatus(FAILURE);
		});
	}
}
