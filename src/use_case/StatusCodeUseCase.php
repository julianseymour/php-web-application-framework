<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\request;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkResponder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class StatusCodeUseCase extends UseCase{

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "error";
	}

	public function getPageContent(): ?array{
		$f = __METHOD__;
		$print = false;
		$status = $this->getObjectStatus();
		if($print){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} status is \"{$err}\"");
		}
		Debug::print("{$f} returning normally");
		return [
			ErrorMessage::getVisualError($status)
		];
	}

	public function getObjectStatus(): int{
		return $this->hasObjectStatus() ? parent::getObjectStatus() : getInputParameter("status", $this);
	}

	protected function getTransitionFromPermission(){
		return SUCCESS;
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function getUriSegmentParameterMap(): ?array{
		return [
			"action",
			"status"
		];
	}

	public static function hasMenu(): bool{
		return true;
	}

	public function execute(): int{
		return $this->getObjectStatus();
	}

	public function hasSwitchUseCase(int $status): bool{
		return false;
	}

	public function getSwitchUseCase(int $status){
		$f = __METHOD__;
		Debug::error("{$f} wtf are you doing here?");
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		if(request()->getProgressiveHyperlinkFlag()){
			if($print){
				Debug::print("{$f} returning ProgressiveHyperlinkResponder");
			}
			return new ProgressiveHyperlinkResponder();
		}elseif($print){
			Debug::print("{$f} nothing to do here");
		}
		return new Responder();
	}
}
