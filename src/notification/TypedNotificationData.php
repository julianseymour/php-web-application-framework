<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\push;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;
use Exception;
use mysqli;

abstract class TypedNotificationData extends NotificationData implements StaticSubtypeInterface, TemplateContextInterface{

	use StaticSubtypeTrait;
	
	public abstract static function getNotificationLinkUriStatic($that);

	public abstract static function getNotificationTypeStatic();

	public abstract static function isDismissableStatic($that);

	public abstract static function getNotificationActionsStatic($that, $index = null);

	public abstract static function noCorrespondent();

	public abstract static function getNotificationTypeString();

	public abstract static function getNotificationUpdateMode();

	public abstract static function getIntersections();

	public abstract static function isCorrespondentObjectRequired();

	public function __construct(?int $mode = ALLOCATION_MODE_EAGER){
		parent::__construct($mode);
		$this->setNotificationType(static::getNotificationTypeStatic());
	}

	public static function getJavaScriptClassPath():?string{
		$f = __METHOD__;
		$print = false;
		$fn = get_class_filename(static::class);
		$ret = substr($fn, 0, strlen($fn) - 3) . "js";
		if($print){
			Debug::print("{$f} returning \"{$ret}\"");
		}
		return $ret;
	}
	
	public static function getSubtypeStatic():string{
		return static::getNotificationTypeStatic();
	}
	
	public static function dismissalRequiresCorrespondentKey($context):bool{
		return false;
	}

	public function hasElementClass():bool{
		return true;
	}

	public function getSubjectClass():string{
		if ($this->hasSubjectData()) {
			return parent::getSubjectClass();
		}
		return static::getSubjectClassStatic($this);
	}

	public static function getJavaScriptClassIdentifier(): string{
		return static::getNotificationTypeStatic();
	}

	public function getSubjectDataType():string{
		$f = __METHOD__;
		$type = $this->getColumnValue('subjectDataType');
		if (isset($type) && $type != "") {
			return $type;
		} elseif ($this->hasSubjectData()) {
			$target = $this->getSubjectData();
			$type = $target->getDataType();
			return $this->setSubjectDataType($type);
		}
		Debug::warning("{$f} non-static target type is undefined");
		$type = static::getSubjectDataTypeStatic();
		return $this->setSubjectDataType($type);
	}

	public final function getTypedNotificationClass():string{
		return static::class;
	}

	public function getSubtype():string{
		if($this->hasColumnValue('subtype')) {
			return $this->getColumnValue('subtype');
		}
		return $this->setSubtype(static::getSubypeStatic());
	}
	
	public function getNotificationType():string{
		return $this->getSubtype();
	}

	public static function getPushStatusVariableName():string{
		$f = __METHOD__;
		$t = static::getNotificationTypeStatic();
		/*while ($t instanceof ValueReturningCommandInterface) {
			$t = $t->evaluate();
		}*/
		$t[0] = strtoupper($t[0]);
		if (starts_with($t, "translate")) {
			Debug::error("{$f} translate command ");
		}
		$vn = "push{$t}Notifications";
		// Debug::print("{$f} returning \"{$vn}\"");
		return $vn;
	}

	public static function getEmailStatusVariableName():string{
		$f = __METHOD__;
		$t = static::getNotificationTypeStatic();
		/*while ($t instanceof ValueReturningCommandInterface) {
			$t = $t->evaluate();
		}*/
		$t[0] = strtoupper($t[0]);
		if (starts_with($t, "translate")) {
			Debug::error("{$f} translate command ");
		}
		$vn = "email{$t}Notifications";
		// Debug::print("{$f} returning \"{$vn}\"");
		return $vn;
	}

	public static function canDisable(){
		return true;
	}

	/**
	 * send this notification to its destination
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function send(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			if($print){
				Debug::print("{$f} user ".$this->getUserData()->getUnambiguousName());
			}
			$user = $this->getUserData();
			// check for duplicate entry, because if it happens in insert and exception will get thrown
			$status = $this->preventDuplicateEntry($mysqli);
			if ($status === ERROR_DUPLICATE_ENTRY) {
				Debug::warning("{$f} duplicate entry found");
				switch ($this->getDuplicateEntryRecourse()) {
					case RECOURSE_IGNORE:
						if ($print) {
							Debug::print("{$f} ignoring database write, but continuing with push notification");
						}
						break;
					case RECOURSE_ABORT:
					case RECOURSE_RETRY:
					case RECOURSE_EXIT:
					default:
						Debug::warning("{$f} duplicate entry disallowed");
						Debug::printStackTrace();
						return $this->setObjectStatus($status);
				}
			} else { // insert notification
				if ($print) {
					$nc = $this->getClass();
					Debug::print("{$f} object of class \"{$nc}\" is not a duplicate entry");
				}
				$status = $this->insert($mysqli);
				if ($print) {
					Debug::print("{$f} returned from writing notification to database");
				}
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} inserting notification returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("{$f} write operation successful");
				}
			}
			// send email notification if applicable
			$notification_type = $this->getNotificationType();
			if (static::getNotificationTypeStatic() === NOTIFICATION_TYPE_TEST) {
				$email_notif_enabled = true;
			} else {
				$email_notif_enabled = $user->getEmailNotificationStatus($notification_type);
			}
			$subject = $this->getSubjectData();
			if($print){
				$subject_class = get_short_class($subject);
				Debug::print("{$f} about to call {$subject_class}->isEmailNotificationWarranted()");
			}
			$warranted = $subject->isEmailNotificationWarranted($user);
			if (! $email_notif_enabled) {
				if ($print) {
					Debug::print("{$f} user has email notifications disabled");
				}
			} elseif (! $warranted) {
				$tcn = $user->getClass();
				if ($print) {
					Debug::print("{$f} email notification is unwarranted according to user of class \"{$tcn}\"");
				}
			} elseif ($email_notif_enabled && $warranted) {
				if ($print) {
					Debug::print("{$f} about to send email notification");
				}
				$status = $user->sendEmail($mysqli, $subject);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} sending email notification returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("{$f} notification email sent successfully");
				}
			} elseif ($print) {
				Debug::print("{$f} about to create a blank reloaded notification data object");
			}
			// send push notification if applicable
			if ($user instanceof PlayableUser && (! $this->canDisable() || $user->getPushNotificationStatus($notification_type))) {
				if ($print) {
					Debug::print("{$f} yes, push notifications are enabled");
				}
				if($subject->isPushNotificationWarranted($user)){
					if ($print) {
						Debug::print("{$f} the object says it warrants a push notification");
					}
					$status = push()->enqueue($this);
					if ($print) {
						Debug::print("{$f} enqueued push notification");
					}
				}elseif($print){
					Debug::print("{$f} push notifications are enabled, but object of class \"{$subject_class}\" says it is unwarranted");
				}
			} elseif ($print) {
				Debug::print("{$f} push notifications are disabled");
			}
			return $this->setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch ($column_name) {
			case "actions":
				return true;
			case 'subtype':
				RETURN true;
			case "title":
				return true;
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		try {
			switch ($column_name) {
				case 'subtype':
					return $this->getSubtypeStatic();
				default:
					return parent::getVirtualColumnValue($column_name);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
	
	public function template(){
		return;
	}
}
