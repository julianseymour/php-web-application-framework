<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\access;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyTrait;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\SecurityNotificationData;

abstract class RequestEvent extends UserFingerprint implements EmailNoteworthyInterface{

	use EmailNoteworthyTrait;

	/**
	 *
	 * @return boolean
	 */
	public abstract function isSecurityNotificationWarranted();

	public abstract static function getReasonLoggedStatic();

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"disableNotification",
			"dismissNotification"
		]);
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public function getUpdateNotificationRecipient():UserData{
		return $this->getUserData();
	}

	public function isNotificationDataWarranted(PlayableUser $user): bool{
		return true;
	}

	public static function getNotificationClass(): string{
		return SecurityNotificationData::class;
	}

	public function isEmailNotificationWarranted(UserData $recipient): bool{
		return true;
	}

	public function getConfirmationUri(){
		return '/account_firewall';
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$f = __METHOD__;
		$config = parent::getArrayMembershipConfiguration($config_id);
		switch ($config_id) {
			case "default":
				if($this->hasColumn("insertIpAddress")) {
					$config['insertIpAddress'] = true;
				}
			default:
				return $config;
		}
	}

	public function getNotificationPreview(){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$columns["userTemporaryRole"]->volatilize();
	}
}
