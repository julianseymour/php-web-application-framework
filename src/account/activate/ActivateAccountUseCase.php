<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

class ActivateAccountUseCase extends ValidConfirmationCodeUseCase
{

	public function execute(): int
	{
		$f = __METHOD__; //ActivateAccountUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$user = user();
			$user->setActivationTimestamp(time());
			$user->getColumn("activationTimestamp")->setUpdateFlag(true);
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $user->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} activation returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return $this->setObjectStatus(RESULT_ACTIVATE_SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getUseCaseId()
	{
		return USE_CASE_ACTIVATE;
	}

	public function getActionAttribute(): ?string
	{
		return '/activate';
	}
}
