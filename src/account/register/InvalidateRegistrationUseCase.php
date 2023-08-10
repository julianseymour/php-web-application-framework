<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
ErrorMessage::unimplemented(__FILE__);

class InvalidateRegistrationUseCase extends UseCase
{

	public function execute(): int
	{
		$f = __METHOD__; //InvalidateRegistrationUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		ErrorMessage::unimplemented($f);
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getUseCaseId()
	{
		return USE_CASE_INVALIDATE_REGISTRATION;
	}

	public function getActionAttribute(): ?string
	{
		return "/invalidate";
	}

	protected function getExecutePermissionClass()
	{
		return AnonymousAccountTypePermission::class;
	}
}
