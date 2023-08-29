<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\AuthenticatedConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class PreActivationConfirmationCode extends AuthenticatedConfirmationCode{

	public static function getSentEmailStatus(){
		return SUCCESS;
	}

	public function setEmailAddress($email){
		return $email;
	}

	public static function getConfirmationUriStatic($suffix){
		return "https://" . WEBSITE_DOMAIN . "/activate/{$suffix}";
	}

	public function setName($name){
		return $name;
	}

	public function isSecurityNotificationWarranted(){
		return false;
	}

	public static function getEmailNotificationClass(){
		return ActivationEmail::class;
	}

	public static function getConfirmationCodeTypeStatic(){
		return ACCESS_TYPE_ACTIVATION;
	}

	public static function getReasonLoggedStatic(){
		return BECAUSE_REGISTER;
	}

	public static function getPermissionStatic(string $name, $data){
		$f = __METHOD__;
		$print = false;
		switch ($name) {
			case DIRECTIVE_INSERT:
				if ($print) {
					Debug::print("{$f} returning new owner permission");
				}
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}
}
