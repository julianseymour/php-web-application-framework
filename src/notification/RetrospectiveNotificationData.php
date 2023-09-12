<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubtypeColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;
use Exception;
use mysqli;

class RetrospectiveNotificationData extends NotificationData implements StaticElementClassInterface, TemplateContextInterface{
	
	use SubtypeColumnTrait;
	
	public function getMessageBox(){
		return $this->getSubjectData()->getMessageBox();
	}

	public function getSubjectClass():string{
		return $this->getColumn("subjectKey")->getForeignDataStructureClass();
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$config = parent::getArrayMembershipConfiguration($config_id);
		switch ($config_id) {
			case "default":
				if($this->hasSubjectData()) {
					$config['subjectKey'] = $this->getSubjectData()->getArrayMembershipConfiguration($config_id);
				}else{
					$config['subjectKey'] = true;
				}
			default:
				return $config;
		}
	}

	public function getNotificationTypeString(){
		return $this->getTypedNotificationClass()::getNotificationTypeString(null);
	}

	public static function getDismissedNotificationState(){
		return NOTIFICATION_STATE_DISMISSED;
	}

	public function dismiss(mysqli $mysqli){
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				Debug::print("{$f} about to update notification state");
			}
			$this->setNotificationState(NOTIFICATION_STATE_DISMISSED);
			$status = $this->update($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} notification status update returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			if($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return $that->getTypedNotificationClass()::getElementClassStatic($that);
	}

	public function getTypedNotificationClass():string{
		$f = __METHOD__;
		$type = $this->getNotificationType();
		if(!is_string($type)) {
			$key = $this->hasIdentifierValue() ? $this->getIdentifierValue() : "[undefined]";
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} notification type \"{$type}\" is not a string. Key is \"{$key}\", declared {$decl}");
		}
		return mods()->getTypedNotificationClass($type);
	}

	public static function getRecentNotificationSelectStatement(){
		return RetrospectiveNotificationData::selectStatic()->where(new AndCommand(RetrospectiveNotificationData::whereIntersectionalHostKey(user()->getClass(), "userKey"), new WhereCondition("updatedTimestamp", OPERATOR_GREATERTHAN)))
			->orderBy(new OrderByClause("pinnedTimestamp", DIRECTION_DESCENDING), new OrderByClause("updatedTimestamp", DIRECTION_DESCENDING), new OrderByClause("insertTimestamp", DIRECTION_DESCENDING));
	}

	public function template(){
		$this->setNotificationType(NOTIFICATION_TYPE_TEMPLATE);
	}
}
