<?php
namespace JulianSeymour\PHPWebApplicationFramework\cache\server;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ClearServerCacheUseCase extends UseCase
{

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getActionAttribute(): ?string
	{
		return "/server_cache";
	}

	protected function getExecutePermissionClass()
	{
		return AdminOnlyAccountTypePermission::class;
	}

	public function getUseCaseId()
	{
		return USE_CASE_CLEAR_SERVER_CACHE;
	}

	public function execute(): int
	{
		$f = __METHOD__; //ClearServerCacheUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		$print = false;
		if (! hasInputParameter('clear')) {
			if ($print) {
				Debug::print("{$f} nothing to do here");
			}
			return SUCCESS;
		}
		$which = getInputParameter("clear");
		if (! is_string($which) || empty($which)) {
			Debug::warning("{$f} invalid or empty string parameter");
			Debug::printArray(getInputParameters());
			Debug::printStackTrace();
		}
		$which = strtolower($which);
		switch ($which) {
			case CONST_ALL:
				cache()->clear();
				break;
			case "apcu":
				cache()->clearAPCu();
				break;
			case "file":
				cache()->clearFile();
				break;
			default:
				Debug::error("{$f} invalid cache \"{$which}\"");
		}
		if ($print) {
			Debug::print("{$f} cache cleared successfully");
		}
		return SUCCESS;
	}

	public function getPageContent(): ?array
	{
		return [
			new ClearServerCacheForm(ALLOCATION_MODE_LAZY)
		];
	}
}
