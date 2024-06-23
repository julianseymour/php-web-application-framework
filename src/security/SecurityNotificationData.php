<?php

namespace JulianSeymour\PHPWebApplicationFramework\security;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\notification\TypedNotificationData;
use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttemptClassResolver;
use Exception;

/**
 * notification of an account security-related event
 *
 * @author j
 */
class SecurityNotificationData extends TypedNotificationData{

	public static function getPermissionStatic(string $name, $data){
		if($data->hasPermission($name)){
			return $data->getPermission($name);
		}
		switch($name){
			case DIRECTIVE_INSERT:
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public static function isCorrespondentObjectRequired(){
		return false;
	}

	public function getName():string{
		return _("Security notification");
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try{
			$this->setNotificationType(NOTIFICATION_TYPE_SECURITY);
			return parent::afterGenerateInitialValuesHook();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getDuplicateEntryRecourse(): int{
		return RECOURSE_IGNORE;
	}

	public static function getNotificationTypeStatic(){
		return NOTIFICATION_TYPE_SECURITY;
	}

	public static function getNotificationLinkUriStatic($that){
		return '/account_firewall';
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return SecurityNotificationElement::class;
	}

	public static function isDismissableStatic($that){
		return true;
	}

	public static function getNotificationActionsStatic($that, $index = null){
		return [];
	}

	public static function noCorrespondent(){
		return true;
	}

	public static function getNotificationTypeString(){
		return _("Security");
	}

	public static function getNotificationUpdateMode(){
		return NOTIFICATION_MODE_SEND_NEW;
	}

	public static function getIntersections():array{
		return AccessAttemptClassResolver::getIntersections();
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch($column_name){
			case "linkUri":
			case "preview":
				return true;
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}
}
