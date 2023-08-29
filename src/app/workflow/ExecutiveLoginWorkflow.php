<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\workflow;

use JulianSeymour\PHPWebApplicationFramework\account\login\ExecutiveLoginUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\ExecutiveMultifactorAuthenticationUseCase;

class ExecutiveLoginWorkflow extends UniversalWorkflow{
	
	protected static function getLoginUseCaseClass():string{
		return ExecutiveLoginUseCase::class;
	}
	
	protected static function getMultifactorAuthenticationUseCaseClass():string{
		return ExecutiveMultifactorAuthenticationUseCase::class;
	}
}
