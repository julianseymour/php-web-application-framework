<?php

namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class UpdateLanguageSettingsUseCase extends UseCase{

	public function execute(): int{
		return LanguageSettingsData::updateLanguageSettingsStatic($this);
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/language";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	protected function getTransitionFromPermission(){
		return SUCCESS;
	}

	public function getResponder($status):?Responder{
		if($status !== SUCCESS){
			return parent::getResponder($status);
		}
		return new SetLanguageResponder();
	}
	
	public function getPageContent():?array{
		if($this->hasPredecessor()){
			return $this->getPredecessor()->getPageContent();
		}
		return parent::getPageContent();
	}
}
