<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissAllNotificationsForm;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class NotificationListElement extends DivElement
{

	use StyleSheetPathTrait;
	
	protected function afterConstructorHook(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null): int
	{
		$ret = parent::afterConstructorHook($mode, $context);
		$this->addClassAttribute("notification_list", "background_color_4");
		$this->setIdAttribute("notification_list");
		if (! Request::isXHREvent()) {
			$this->setAttribute("loaded", 1);
		}
		return $ret;
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //NotificationListElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try {
			$print = false;
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$pin_notification_here = new DivElement($mode);
			$pin_notification_here->setIdAttribute("pin_notification_here");
			$pin_notification_here->addClassAttribute("hidden");
			$pin_notification_here->setAllowEmptyInnerHTML(true);
			$this->appendChild($pin_notification_here);
			$insert_notification_here = new DivElement($mode);
			$insert_notification_here->setIdAttribute("insert_notification_here");
			$insert_notification_here->setAllowEmptyInnerHTML(true);
			$this->appendChild($insert_notification_here);
			$count = $context->getNotificationCount();
			// $tab_counters = [];
			if ($count > 0) {
				$notifications = $context->getNotifications();
				foreach ($notifications as $note_key => $note) {
					if ($note === null) {
						Debug::error("{$f} notification object is null");
					} elseif (! $note->hasUserData()) {
						Debug::error("{$f} notification does not have its user data");
					}
					if ($note->hasSubjectData()) {
						$subject = $note->getSubjectData();
						if ($subject === null) {
							if ($note->hasSubjectKey()) {
								Debug::print("{$f} notification has subject key");
							} else {
								$key = $note->getIdentifierValue();
								Debug::print("{$f} notification with key \"{$key}\" does not have a subject key");
							}
							$note_class = $note->getClass();
							Debug::error("{$f} {$note_class} with key \"{$note_key}\" subject was not loaded at all");
						} elseif ($subject->getObjectStatus() === STATUS_PRELAZYLOAD) {
							$subject_class = $subject->getClass();
							$subject_key = $subject->getIdentifierValue();
							$decl = $subject->getDeclarationLine();
							Debug::error("{$f} subject of class \"{$subject_class}\" with key \"{$subject_key}\" was never properly loaded. Instantiated {$decl}");
						}
						$note_type = $note->getNotificationType();
						if ($note_type === NOTIFICATION_TYPE_SECURITY) {
							if ($print) {
								Debug::print("{$f} this is a security notification");
							}
						} elseif ($print && ! $subject->hasUserData()) {
							$subject_key = $subject->getIdentifierValue();
							$subject_class = $subject->getClass();
							$user_key = $subject->getUserKey();
							$account_type = $subject->getUserAccountType();
							Debug::warning("{$f} subject object of class \"{$subject_class}\" with key \"{$subject_key}\", user key \"{$user_key}\" and user account type \"{$account_type}\" lacks user data");
						}
					} else {
						if ($print) {
							Debug::print("{$f} notification subject is missing, skipping it");
						}
						continue;
					}
					$state = $note->getNotificationState();
					if ($state !== NOTIFICATION_STATE_UNREAD) {
						if ($print) {
							Debug::print("{$f} notification is not unread ({$state}), continuing");
						}
						continue;
					} elseif ($print) {
						Debug::print("{$f} notification is unread -- echoing its report");
					}
					$iec = $note->getElementClass();
					$element = new $iec($mode, $note);
					$element->setDisposeContextFlag(true);
					$this->appendChild($element);
				}
			}
			// $this->setTabCounters($tab_counters);
			$dismiss_all_form = new DismissAllNotificationsForm($mode, $context);
			$this->appendChild($dismiss_all_form);
			return $this->getChildNodes();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
