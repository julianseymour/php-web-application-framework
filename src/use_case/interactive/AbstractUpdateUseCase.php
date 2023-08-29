<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\CounterpartKeyColumnInterface;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\FiniteStatefulNoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\notification\NoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;

abstract class AbstractUpdateUseCase extends SubsequentUseCase{

	protected abstract function processForm(DataStructure $updated_object): int;

	public function execute(): int{
		$f = __METHOD__;
		try {
			$print = false;
			$updated_object = $this->getPredecessor()->getDataOperandObject();
			// 1. replicate operand
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if (! isset($mysqli)) {
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::error("{$f} {$err}");
			}
			$backup = $updated_object->replicate();
			$pred = $this->getPredecessor();
			$pred->reconfigureDataStructure($backup);
			if ($backup instanceof DataStructure && $backup->getDataType() !== DATATYPE_USER) {
				$generator = $this->getLoadoutGenerator(user());
				if($generator instanceof LoadoutGenerator){
					$loadout = $generator->generateNonRootLoadout($backup, $this);
					if ($loadout instanceof Loadout) {
						$status = $loadout->expandTree($mysqli, $backup);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} expanding backup returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
						}
					}
				}elseif($print){
					Debug::print("{$f} loadout generator class is null");
				}
			}
			$pred->setOriginalOperand($backup);
			// 2. process form
			$status = $this->processForm($updated_object);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} processing form returned error status \"{$err}\"");
				if ($updated_object->getDeleteForeignDataStructuresFlag()) {
					Debug::error("{$f} which it should not be doing if there are foreign data structures to delete");
				} elseif ($print) {
					Debug::print("{$f} deleteForeignDataStructures flag is not set");
				}
				return $this->setObjectStatus($status);
			}
			// 3. update
			$status = $updated_object->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} update returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// 4. update user's notification, and send the other party a notification if necessary
			if ($updated_object instanceof NoteworthyInterface) {
				if ($print) {
					Debug::print("{$f} data operand is noteworthy");
				}
				$status = $this->updateNotificationData($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} updateNotificationData returned error status \"{$err}\"");
				} elseif ($print) {
					Debug::print("{$f} successfully updated notification data");
				}
			} elseif ($print) {
				Debug::print("{$f} data operand is not noteworthy");
			}
			$backup->setObjectStatus($updated_object->getObjectStatus());
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function updateNotificationData($mysqli){
		$f = __METHOD__;
		try {
			$print = false;
			$updated_object = $this->getPredecessor()->getDataOperandObject();
			if ($updated_object->getDismissNotificationFlag()) {
				$status = $updated_object->dismissYourNotification($mysqli);
			} elseif ($print) {
				Debug::print("{$f} updated object does not have its dismiss notification flag set");
			}
			if ($updated_object instanceof CounterpartKeyColumnInterface) { // XXX TODO: the reason the user is not receiving notifications when administrator updates his orders is because they do not have counterparts; find a solution
				if ($print) {
					Debug::print("{$f} data operand is an instanceof CounterpartKeyColumnInterface");
				}
				$counterpart = $updated_object->getCounterpartObject();
				// send the other party a notification
				if ($updated_object->getSendCounterpartNotificationFlag() && $counterpart->sendNotificationOnStatusUpdate() && ! $counterpart->getDisableNotificationsFlag()) {
					if ($print) {
						Debug::print("{$f} sending status update notification");
					}
					$status = $counterpart->reload($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} reloading counterpart returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
					$correspondent = $counterpart->getUpdateNotificationRecipient();
					if ($correspondent instanceof PlayableUser) {
						$correspondent->setTemporaryRole(USER_ROLE_RECIPIENT);
						if (! $correspondent->hasTemporaryRole()) {
							Debug::error("{$f} correspondent lacks a role");
						} elseif ($print) {
							$correspondent_name = $correspondent->getName();
							Debug::print("{$f} about to send a notification to \"{$correspondent_name}\"");
						}
						$status = $correspondent->notifyIfWarranted($mysqli, $counterpart);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} sending notification returned error status \"{$err}\"");
						}
					} elseif ($print) {
						Debug::print("{$f} correspondent is non-interactable");
					}
				} elseif ($print) {
					Debug::print("{$f} data operand does not have its send counterpart notification flag set, or counterpart does not accept notifications on status update");
				}
			} elseif ($updated_object instanceof FiniteStatefulNoteworthyInterface) {
				// copied from above
				$recipient = $updated_object->getUpdateNotificationRecipient();
				if ($recipient instanceof PlayableUser) {
					$recipient->setTemporaryRole(USER_ROLE_RECIPIENT);
					if (! $recipient->hasTemporaryRole()) {
						Debug::error("{$f} recipient lacks a role");
					} elseif ($print) {
						$recipient_name = $recipient->getName();
						Debug::print("{$f} about to send a notification to \"{$recipient_name}\"");
					}
					$status = $recipient->notifyIfWarranted($mysqli, $updated_object);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} sending notification returned error status \"{$err}\"");
					}
				} elseif ($print) {
					Debug::print("{$f} recipient is non-interactable");
				}
			} elseif ($print) {
				Debug::print("{$f} data operand is not an instanceof CounterpartKeyColumnInterface");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
