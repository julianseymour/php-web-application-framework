<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth;

use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttempt;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressEmail;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class ReauthenticationEvent extends AccessAttempt implements StaticTableNameInterface{

	use StaticTableNameTrait;
	
	protected $loginSuccessful;

	public function getLoginSuccessful(){
		return $this->loginSuccessful;
	}

	public static function getSubtypeStatic(): string{
		return ACCESS_TYPE_REAUTHENTICATION;
	}

	public function isSecurityNotificationWarranted():bool{
		return false;
	}

	public static function getEmailNotificationClass():?string{
		return UnlistedIpAddressEmail::class;
	}

	public function setLoginSuccessful($value){
		return $this->loginSuccessful = $value;
	}

	public function setLoginResult($status){
		return $this->setObjectStatus($status);
	}

	public function getLoginResult():int{
		return $this->getObjectStatus();
	}

	public static final function getPhylumName(): string{
		return "reauthentications";
	}

	public static function getPrettyClassName():string{
		return _("Reauthentication attempt");
	}

	public static function getPrettyClassNames():string{
		return _("Reauthentication attempts");
	}

	public static function getTableNameStatic(): string{
		return "reauthentications";
	}

	public static function getReasonLoggedStatic(){
		return BECAUSE_REAUTH;
	}
}
