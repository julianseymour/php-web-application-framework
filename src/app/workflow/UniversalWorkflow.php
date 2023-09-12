<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\workflow;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\UniversalLoginUseCase;
use JulianSeymour\PHPWebApplicationFramework\account\logout\LogoutUseCase;
use JulianSeymour\PHPWebApplicationFramework\admin\login\AdminLoginUseCase;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\UniversalMfaUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\password\reset\ForgotCredentialsUseCase;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\language\settings\UpdateLanguageSettingsUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class UniversalWorkflow extends StandardWorkflow{

	protected static function getLoginUseCaseClass():string{
		return UniversalLoginUseCase::class;
	}
	
	protected static function getMultifactorAuthenticationUseCaseClass():string{
		return UniversalMfaUseCase::class;
	}
	
	protected function authenticate(Request $request, UseCase $entry_point): int{
		$f = __METHOD__;
		try{
			$print = false;
			$directive = directive();
			switch ($directive) {
				case DIRECTIVE_FORGOT_CREDENTIALS:
					if($print) {
						Debug::print("{$f} handing request over to ForgotCredentials");
					}
					$forgot = new ForgotCredentialsUseCase($entry_point);
					$forgot->validateTransition();
					return $this->execute($request, $forgot);
				case DIRECTIVE_LOGIN:
					if($print) {
						Debug::print("{$f} user is logging in");
					}
					$login_class = $this->getLoginUseCaseClass();
					$login = new $login_class($entry_point);
					$login->validateTransition();
					return $this->execute($request, $login);
				case DIRECTIVE_ADMIN_LOGIN:
					if($print) {
						Debug::print("{$f} admin login");
					}
					$login = new AdminLoginUseCase($entry_point);
					$login->validateTransition();
					return $this->execute($request, $login);
				case DIRECTIVE_MFA:
					if($print) {
						Debug::print("{$f} user is submitting a MFA OTP");
					}
					$mfa_class = $this->getMultifactorAuthenticationUseCaseClass();
					$mfa = new $mfa_class($entry_point);
					$mfa->validateTransition();
					return $this->execute($request, $mfa);
				default:
			}
			$ret = parent::authenticate($request, $entry_point);
			switch ($directive) {
				case DIRECTIVE_LOGOUT:
					if($print) {
						Debug::print("{$f} user is logging out");
					}
					$logout = new LogoutUseCase($entry_point);
					$logout->setObjectStatus($logout->validateTransition());
					return $this->execute($request, $logout);
				case DIRECTIVE_LANGUAGE:
					if($print) {
						Debug::print("{$f} about to update language settings");
					}
					$language = new UpdateLanguageSettingsUseCase($entry_point);
					$language->validateTransition();
					return $this->execute($request, $language);
			}
			return $ret;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}

