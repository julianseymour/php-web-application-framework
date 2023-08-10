<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\CustomerAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ResendActivationEmailUseCase extends UseCase
{

	public function execute(): int
	{
		$f = __METHOD__; //ResendActivationEmailUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$status = parent::execute();
			$user = user();
			if ($user == null) {
				Debug::error("{$f} user data returned null");
				return $this->setObjectStatus(ERROR_NULL_USER_OBJECT);
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = PreActivationConfirmationCode::submitStatic($mysqli, $user);
			return $status;
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
		return USE_CASE_RESEND_ACTIVATION;
	}

	public function getActionAttribute(): ?string
	{
		return "/resend_activation";
	}

	protected function getExecutePermissionClass()
	{
		return CustomerAccountTypePermission::class;
	}
}
