<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\CustomerAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidateAuthenticatedConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterLoadEvent;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\ExecutiveLoginWorkflow;

class ValidateAccountActivationCodeUseCase extends ValidateAuthenticatedConfirmationCodeUseCase{

	public static function getBruteforceAttemptClass(): string{
		return ActivationAttempt::class;
	}

	public static function getConfirmationCodeClass(): string{
		return PreActivationConfirmationCode::class;
	}

	public function setObjectStatus(?int $status):?int{
		$f = __METHOD__; 
		try {
			if ($status === ERROR_ALREADY_LOGGED) {
				Debug::error("{$f} so what if I'm already logged in?");
			}
			return parent::setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getPageContent(): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$status = $this->getObjectStatus();
			if ($status === ERROR_MUST_LOGIN && ! Request::isXHREvent()) {
				if ($print) {
					Debug::print("{$f} about to return activation form");
				}
				$user_class = mods()->getUserClass(NormalUser::getAccountTypeStatic());
				$context = new $user_class();
				$form = new ActivationForm(ALLOCATION_MODE_LAZY, $context);
				if (empty($form)) {
					Debug::error("{$f} activation form returned null");
				} elseif ($print) {
					Debug::print("{$f} returning a nifty form");
				}
				$this->setObjectStatus(SUCCESS);
				return [
					$form
				];
			} elseif ($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} parent function returned error status \"{$err}\"");
			}
			if ($print) {
				Debug::print("{$f} returning parent function");
			}
			return parent::getPageContent();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/activate";
	}

	public function getFormClass(): ?string{
		return ActivationForm::class;
	}

	public static function validateOnFormSubmission(): bool{
		return false;
	}

	protected function initializeSwitchUseCases(): ?array{
		return [
			SUCCESS => ActivateAccountUseCase::class
		];
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public function beforeExecuteHook():int{
		$ret = parent::beforeExecuteHook();
		if(user() instanceof AnonymousUser){
			return ERROR_MUST_LOGIN;
		}
		return $ret;
	}
	
	public static function getDefaultWorkflowClass():string{
		return ExecutiveLoginWorkflow::class;
	}
}
