<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

class ExecutiveMultifactorAuthenticationUseCase extends UniversalMfaUseCase{
	
	public function execute():int{
		$status = parent::execute();
		if($status === SUCCESS){
			return $this->getPredecessor()->execute();
		}
		return $status;
	}
	
	public function getSwitchUseCase(int $status){
		if($status === SUCCESS){
			return $this->getPredecessor()->getSwitchUseCase($status);
		}
		return null;
	}
	
	public function hasSwitchUseCase(int $status): bool{
		return $status === SUCCESS;
	}
}
