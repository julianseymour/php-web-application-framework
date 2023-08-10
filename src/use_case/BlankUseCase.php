<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

class BlankUseCase extends UseCase
{

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getActionAttribute(): ?string
	{
		return "/blank";
	}

	public function getUseCaseId()
	{
		return USE_CASE_BLANK;
	}

	public function getPageContent(): ?array
	{
		return [
			_("This page intentionally left blank")
		];
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}
}
