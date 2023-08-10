<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\access;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyTrait;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\SecurityNotificationData;

abstract class RequestEvent extends UserFingerprint implements EmailNoteworthyInterface
{

	use EmailNoteworthyTrait;

	/**
	 *
	 * @return boolean
	 */
	public abstract function isSecurityNotificationWarranted();

	public abstract static function getIpLogReason();

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"disableNotification",
			"dismissNotification"
		]);
	}

	public function hasParentKey()
	{
		return $this->hasUserKey();
	}

	public static function throttleOnInsert(): bool
	{
		return false;
	}

	public function getUpdateNotificationRecipient()
	{
		return $this->getUserData();
	}

	public function isNotificationDataWarranted(PlayableUser $user): bool
	{
		return true;
	}

	public static function getNotificationClass(): string
	{
		return SecurityNotificationData::class;
	}

	public function isEmailNotificationWarranted($recipient): bool
	{
		return true;
	}

	public function getConfirmationUri()
	{
		return '/account_firewall';
	}

	public function getArrayMembershipConfiguration($config_id): ?array
	{
		$f = __METHOD__; //RequestEvent::getShortClass()."(".static::getShortClass().")->getArrayMembershipConfiguration()";
		$config = parent::getArrayMembershipConfiguration($config_id);
		switch ($config_id) {
			case "default":
				if ($this->hasColumn("insertIpAddress")) {
					$config['insertIpAddress'] = true;
				}
			default:
				return $config;
		}
	}

	/*
	 * protected function afterInsertHook(mysqli $mysqli):int{
	 * $f = __METHOD__; //RequestEvent::getShortClass()."(".static::getShortClass().")->afterInsertHook()";
	 * try{
	 * //Debug::print("{$f} entered");
	 * $status = parent::afterInsertHook($mysqli);
	 * if($status !== SUCCESS){
	 * $err = ErrorMessage::getResultMessage($status);
	 * Debug::warning("{$f} parent function returned error status \"{$err}\"");
	 * return $this->setObjectStatus($status);
	 * }
	 * //Debug::print("{$f} parent function executed successfully");
	 * if(!$this->hasUserData()){
	 * Debug::error("{$f} true client object is undefined");
	 * return $this->setObjectStatus(ERROR_NULL_TRUE_USER_OBJECT);
	 * }
	 * $user = $this->getUserData();
	 * if(!isset($user)){
	 * Debug::error("{$f} getUserData returned null");
	 * /*}elseif(!$user->getSecurityNotificationStatus()){
	 * //Debug::print("{$f} user has security notifications disabled");
	 * return SUCCESS;*\/
	 * }elseif(!$this->isSecurityNotificationWarranted()){
	 * //Debug::print("{$f} security notification is unwarranted");
	 * return SUCCESS;
	 * }
	 * //Debug::print("{$f} security notification is warranted; about to reload this object from the database");
	 * $reloaded = $this->reload($mysqli);
	 * if(!isset($reloaded)){
	 * Debug::error("{$f} reloaded object returned null");
	 * }
	 * $status = $reloaded->getObjectStatus();
	 * if($status !== SUCCESS){
	 * $err = ErrorMessage::getResultMessage($status);
	 * Debug::error("{$f} reloaded object has error status \"{$err}\"");
	 * return $status;
	 * }
	 * $user->setTemporaryRole(USER_ROLE_RECIPIENT);
	 * $status = $user->notify($mysqli, $reloaded);
	 * if($status !== SUCCESS){
	 * return static::debugErrorStatic($f, $status);
	 * }
	 * //Debug::print("{$f} sent security notification successfully");
	 * return SUCCESS;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public function getCommentRoot()
	{
		return $this;
	}

	public function getNotificationPreview()
	{
		$f = __METHOD__; //RequestEvent::getShortClass()."(".static::getShortClass().")->getNotificationPreview()";
		ErrorMessage::unimplemented($f);
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::reconfigureColumns($columns, $ds);
		$columns["userTemporaryRole"]->volatilize();
	}
}
