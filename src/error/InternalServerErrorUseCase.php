<?php
namespace JulianSeymour\PHPWebApplicationFramework\error;

use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class InternalServerErrorUseCase extends UseCase
{

	public function isPageUpdatedAfterLogin(): bool
	{
		return false;
	}

	public function getActionAttribute(): ?string
	{
		return "/500";
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}

	public function getUseCaseId()
	{
		return USE_CASE_ERROR;
	}

	public static function hasMenu(): bool
	{
		return false;
	}

	public function getPageContent(): ?array
	{
		return [
			ErrorMessage::getResultMessage(ERROR_INTERNAL)
		];
	}

	public static function getDefaultWorkflowClass(): string
	{
		return SimpleWorkflow::class;
	}
}
