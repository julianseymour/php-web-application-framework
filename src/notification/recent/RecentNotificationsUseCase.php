<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\recent;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\EnabledAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\poll\PollingUseCaseInterface;
use JulianSeymour\PHPWebApplicationFramework\poll\ShortPollUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class RecentNotificationsUseCase extends UseCase implements ClientUseCaseInterface, PollingUseCaseInterface{

	public function getLoadoutGeneratorClass(?DataStructure $object = null): ?string{
		return RecentNotificationsLoadoutGenerator::class;
	}

	public function getTransitionFromPermission(){
		$f = __METHOD__;
		$print = false;
		return new Permission(DIRECTIVE_TRANSITION_FROM, function ($user, $use_case, $predecessor) use ($f, $print) {
			if($print) {
				$ucc = $use_case->getClass();
				$pc = $predecessor->getClass();
				Debug::print("{$f} entered; use case class is \"{$ucc}\"; predecessor class is \"{$pc}\"");
			}
			return $predecessor instanceof ShortPollUseCase ? SUCCESS : FAILURE;
		});
	}

	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(! hasInputParameter('uniqueKey', $this)) {
				Debug::printPost("{$f} user ID was not posted");
			}elseif($print) {}
			$status = parent::execute();
			switch ($status) {
				case SUCCESS:
				case RESULT_SUBMISSION_ACCEPTED:
				case RESULT_SUBMISSION_REJECTED_FLOOD:
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} parent function returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
			}
			$user = user();
			$key = $user->getIdentifierValue();
			$posted_user_key = getInputParameter('uniqueKey');
			if($key !== $posted_user_key) {
				Debug::print("{$f} current user key \"{$key}\" does not match posted user ID \"{$posted_user_key}\" -- you have been logged out");
				return $this->setObjectStatus(ERROR_XSRF);
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if($mysqli == null) {
				Debug::error("{$f} error connecting client updater");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$user->setNotificationDeliveryTimestamp(time());
			$status = $user->update($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} updating notification delivery timestamp returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	protected function getExecutePermissionClass(){
		return EnabledAccountTypePermission::class;
	}

	public function getActionAttribute(): string{
		return "/poll";
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		try{
			if($status !== SUCCESS){
				return parent::getResponder($status);
			}elseif(! user()->hasForeignDataStructureList("notifications") && ! user()->hasForeignDataStructureList("online")) {
				return parent::getResponder($status);
			}
			return new RecentNotificationsResponder();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getClientUseCaseName(): ?string{
		return "recent_notifications";
	}
}
