<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\AuthenticatedConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class PreActivationConfirmationCode extends AuthenticatedConfirmationCode{

	public static function getSentEmailStatus():int{
		return SUCCESS;
	}

	public function setEmailAddress(string $email):string{
		return $email;
	}

	public static function getConfirmationUriStatic(string $suffix):string{
		return "https://" . DOMAIN_LOWERCASE . "/activate/{$suffix}";
	}

	public function setName(string $name):string{
		return $name;
	}

	public function isSecurityNotificationWarranted():bool{
		return false;
	}

	public static function getEmailNotificationClass():?string{
		return ActivationEmail::class;
	}

	public static function getSubtypeStatic():string{
		return ACCESS_TYPE_ACTIVATION;
	}

	public static function getReasonLoggedStatic():string{
		return BECAUSE_REGISTER;
	}

	public static function getPermissionStatic(string $name, $data){
		$f = __METHOD__;
		$print = false;
		switch($name){
			case DIRECTIVE_INSERT:
				if($print){
					Debug::print("{$f} returning new owner permission");
				}
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}
}
