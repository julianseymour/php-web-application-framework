<?php
namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class UpdateLanguageSettingsUseCase extends UseCase
{

	public function execute(): int
	{
		return LanguageSettingsSessionData::updateLanguageSettingsStatic($this);
	}

	/*
	 * public function getRootNodeTreeSelectStatements():?array{
	 * $predecessor = $this->getPredecessor();
	 * return $predecessor->getRootNodeTreeSelectStatements();
	 * }
	 */
	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getActionAttribute(): ?string
	{
		return "/language";
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}

	public function getUseCaseId()
	{
		return USE_CASE_UPDATE_LANGUAGE_SETTINGS;
	}

	protected function getTransitionFromPermission()
	{
		return SUCCESS;
	}

	public function getResponder(): ?Responder
	{
		return new SetLanguageResponder();
	}
}
