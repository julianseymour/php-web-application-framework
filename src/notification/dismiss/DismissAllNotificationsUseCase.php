<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\dismiss;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class DismissAllNotificationsUseCase extends UseCase implements ClientUseCaseInterface{

	use JavaScriptCounterpartTrait;

	public function execute(): int{
		$f = __METHOD__;
		try{
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if($mysqli == null) {
				Debug::error("{$f} mysql connection failed");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$idn = RetrospectiveNotificationData::getIdentifierNameStatic();
			$query = RetrospectiveNotificationData::selectStatic(null, $idn, "notificationState")->where(new WhereCondition("notificationState", OPERATOR_EQUALS));
			$result = $query->prepareBindExecuteGetResult($mysqli, 's', NOTIFICATION_STATE_UNREAD);
			if($result == null) {
				$status = $query->getObjectStatus();
				Debug::error("{$f} error getting results of prepared query \"{$query}\": got error status \"" . ErrorMessage::getResultMessage($status) . "\"");
				return $status;
			}
			$count = $result->num_rows;
			if($count === 0) {
				Debug::error("{$f} count is 0; why was the dismiss all button even visible then?");
				return $this->setObjectStatus(SUCCESS);
			}
			$results = $result->fetch_all(MYSQLI_ASSOC);
			if($results == null) {
				Debug::error("{$f} results returned null");
				$result->free_result();
				return $query->setObjectStatus(ERROR_MYSQLI_FETCH);
			}
			foreach($results as $r) {
				$key = $r[$idn];
				$note = new RetrospectiveNotificationData();
				$note->loadFromKey($mysqli, $key);
				if(!$note->isDismissable()) {
					// Debug::print("{$f} notification is not dismissable, continuing");
					continue;
				}
				// Debug::print("{$f} about to dismiss notification");
				$status = $note->dismiss($mysqli);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} dismissal function returned error status \"{$err}\"");
					$result->free_result();
					return $this->setObjectStatus($status);
				}
				// Debug::print("{$f} successfully dismissed notification #".$note->getSerialNumber());
			}
			$result->free_result();
			// Debug::print("{$f} done iterating through {$count} notifications");
			$status = $this->getObjectStatus();
			$err = ErrorMessage::getResultMessage($status);
			// Debug::print("{$f} returning normally with status \"{$err}\"");
			return $status; // $this->setObjectStatus($status);
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
		return "/dismiss_all";
	}

	public function getClientUseCaseName(): ?string{
		return "dismiss_all";
	}
}
