<?php

namespace JulianSeymour\PHPWebApplicationFramework\admin\login;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\StandaloneLoginResponder;
use JulianSeymour\PHPWebApplicationFramework\account\login\StandaloneLoginUseCase;
use JulianSeymour\PHPWebApplicationFramework\account\login\UniversalLoginResponder;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\StandaloneMfaResponder;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\UniversalMfaResponder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class AdminLoginUseCase extends StandaloneLoginUseCase{

	public function getAuthenticatedUserClass():?string{
		return config()->getAdministratorClass();
	}

	public function getLoginFormClass():string{
		return AdminLoginForm::class;
	}

	public function execute():int{
		$f = __METHOD__;
		try{
			$print = false;
			$dir = directive();
			if($dir !== DIRECTIVE_ADMIN_LOGIN) {
				if($print) {
					Debug::print("{$f} not attempting an admin login; directive is \"{$dir}\"");
				}
				return $this->setObjectStatus(SUCCESS);
			}elseif($print) {
				Debug::print("{$f} returning parent function output");
			}
			return parent::execute();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getPageContent():array{
		if(user() instanceof AnonymousUser) {
			$admin_class = config()->getAdministratorClass();
			return [
				new AdminLoginForm(ALLOCATION_MODE_LAZY, new $admin_class())
			];
		}elseif(user() instanceof Administrator) {
			return [
				"Welcome administrator"
			];
		}else{
			return [
				"&nbsp"
			];
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}
	
	public function getActionAttribute(): ?string{
		return "/admin_login";
	}

	protected function getExecutePermissionClass(){
		return new AnonymousAccountTypePermission("admin_login");
	}
	
	public function getResponder(int $status):?Responder{
		$f = __METHOD__;
		$print = false;
		switch ($status) {
			case SUCCESS:
				if(user() instanceof AnonymousUser) {
					if($print) {
						Debug::print("{$f} user is unregistered; returning parent function");
					}
					return new Responder();
				}
				if(APPLICATION_INTEGRATION_MODE === APP_INTEGRATION_MODE_UNIVERSAL){
					return new UniversalLoginResponder();
				}
				return new StandaloneLoginResponder();
			case RESULT_BFP_MFA_CONFIRM:
				if(APPLICATION_INTEGRATION_MODE === APP_INTEGRATION_MODE_UNIVERSAL){
					return new UniversalMfaResponder();
				}
				return new StandaloneMfaResponder();
			default:
				if($print) {
					Debug::print("{$f} default case");
				}
				return new Responder();
		}
	}
}
