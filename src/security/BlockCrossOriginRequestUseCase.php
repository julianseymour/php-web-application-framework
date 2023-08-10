<?php
namespace JulianSeymour\PHPWebApplicationFramework\security;

use function JulianSeymour\PHPWebApplicationFramework\f;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class CrossOriginRequestBlocked extends UseCase
{

	public function preload()
	{
		return SUCCESS;
	}

	protected static function skipLoad()
	{
		return true;
	}

	protected static function ignoreIncomingFiles()
	{
		return true;
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return false;
	}

	public function getActionAttribute(): ?string
	{
		return null;
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}

	public function getUseCaseId()
	{
		return USE_CASE_ERROR;
	}

	public function echoResponse(): void
	{
		$f = __METHOD__;
		Debug::warning("{$f} invalid cross-origin request");
		$this->setObjectStatus(ERROR_CROSS_ORIGIN_REQUEST);
		parent::echoResponse();
	}
}
