<?php

namespace JulianSeymour\PHPWebApplicationFramework\cache\server;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class ClearServerCacheUseCase extends UseCase{

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/server_cache";
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}

	public function execute(): int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(! hasInputParameter('clear')){
			if($print){
				Debug::print("{$f} nothing to do here");
			}
			return SUCCESS;
		}
		$which = getInputParameter("clear");
		if(!is_string($which) || empty($which)){
			Debug::warning("{$f} invalid or empty string parameter");
			Debug::printArray(getInputParameters());
			Debug::printStackTrace();
		}
		$which = strtolower($which);
		switch($which){
			case CONST_ALL:
				cache()->clear();
				break;
			case "apcu":
				if($print){
					Debug::print("{$f} clearing APCu cache");
				}
				cache()->clearAPCu();
				break;
			case "file":
				if($print){
					Debug::print("{$f} clearing file cache");
				}
				cache()->clearFile();
				break;
			default:
				Debug::error("{$f} invalid cache \"{$which}\"");
		}
		if($print){
			Debug::print("{$f} cache cleared successfully");
		}
		return SUCCESS;
	}

	public function getPageContent(): ?array{
		if(!user() instanceof Administrator){
			return [
				ErrorMessage::getVisualError(ERROR_EMPLOYEES_ONLY)
			];
		}
		return [
			new ClearServerCacheForm(ALLOCATION_MODE_LAZY)
		];
	}
}
