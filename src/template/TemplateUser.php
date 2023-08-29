<?php

namespace JulianSeymour\PHPWebApplicationFramework\template;


use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use mysqli;

class TemplateUser extends PlayableUser implements TemplateContextInterface{

	public function getHardResetCount(): int{
		return $this->getColumnValue("hardResetCount");
	}

	public function template(){
		$this->setAccountType(ACCOUNT_TYPE_USER);
	}

	public function getHasEverAuthenticated(): bool{
		return $this->getColumnValue("hasEverAuthenticated");
	}

	public static function getAccountTypeStatic(){
		return ACCOUNT_TYPE_TEMPLATE;
	}

	public function getProfileImageData(){
		return $this->getForeignDataStructure("profileImageKey");
	}

	public static function getPrettyClassName():string{
		return NormalUser::getPrettyClassName();
	}

	public function getAttachmentsEnabled(): bool{
		return $this->getColumnValue("attachmentsEnabled");
	}

	public static function getTableNameStatic(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getPrettyClassNames():string{
		return NormalUser::getPrettyClassNames();
	}

	public function filterIpAddress(mysqli $mysqli, ?string $ip_address = null, bool $skip_insert = false): int{
		return FAILURE;
	}
	
	public static function getDefaultPersistenceModeStatic():int{
		return PERSISTENCE_MODE_UNDEFINED;
	}
}
