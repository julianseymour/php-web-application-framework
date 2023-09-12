<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\dismiss;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class DismissNotificationUseCase extends UseCase{

	public function execute(): int{
		$f = __METHOD__;
		try{
			Debug::print("{$f} entered");
			$user = user();
			if($user == null) {
				Debug::error("{$f} user data returned null");
				return $this->setObjectStatus(ERROR_NULL_USER_OBJECT);
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if($mysqli == null) {
				Debug::error("{$f} mysql connection failed");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$notif = new RetrospectiveNotificationData();
			$notif->setUserData($user);
			$notif->loadFromKey($mysqli, getInputParameter($notif->getIdentifierName()));
			if($notif == null || $notif->getObjectStatus() == ERROR_NOT_FOUND) {
				Debug::error("{$f} object not found");
				return $this->setObjectStatus(ERROR_NOT_FOUND);
			}
			$status = $notif->getObjectStatus();
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} adoption returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$notif->loadForeignDataStructures($mysqli, false, 2);
			if($notif->getNotificationState() !== NOTIFICATION_STATE_UNREAD) {
				// $target = $notif->getSubjectData();
				if(!$notif->isDismissable()) {
					Debug::warning("{$f} notification is not dismissable");
					$this->setDataOperandObject($notif);
					return $this->setObjectStatus(ERROR_CANNOT_DISMISS);
				}
				Debug::print("{$f} allowing dismissal");
			}
			if($notif->getUserKey() !== user()->getIdentifierValue()) {
				Debug::warning("{$f} somebody is screwing with post");
				return $this->setObjectStatus(ERROR_FORBIDDEN);
			}
			$status = $notif->dismiss($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} dismissal returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$this->setDataOperandObject($notif);
			Debug::print("{$f} returning notmally");
			return $this->setObjectStatus($status);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/dismiss";
	}

	public function getResponder(int $status): ?Responder{
		if($status === SUCCESS) {
			return new DismissNotificationResponder();
		}
		return parent::getResponder($status);
	}
}
