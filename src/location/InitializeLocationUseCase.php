<?php

namespace JulianSeymour\PHPWebApplicationFramework\location;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class InitializeLocationUseCase extends UseCase{

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/initialize_location";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function execute(): int{
		Debug::printArray(getInputParameters());
		return SUCCESS;
	}
}
