<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

trait NoteworthyTrait{

	use FlagBearingTrait;

	/**
	 *
	 * @return UserData
	 */
	public abstract function getUpdateNotificationRecipient();

	public abstract function getIdentifierName(): ?string;

	public abstract static function getNotificationClass(): string;

	public abstract function getNotificationPreview();

	public abstract function getSubtype():string;

	public abstract function hasSubtype(): bool;

	public abstract function isNotificationDataWarranted(): bool;

	public function getDismissNotificationFlag():bool{
		return $this->getFlag("dismissNotification");
	}

	public function disableNotifications(bool $value = true):bool{
		$this->setDisableNotificationsFlag($value);
		return $this;
	}

	public function setDisableNotificationsFlag(bool $value = true): bool{
		return $this->setFlag("disableNotifications");
	}

	public function getDisableNotificationsFlag():bool{
		return $this->getFlag("disableNotifications");
	}

	public function getSendCounterpartNotificationFlag():bool{
		$f = __METHOD__; //"NoteworthyTrait(".static::getShortClass().")->getSendCounterpartNotificationFlag()";
		$flag = $this->getFlag("sendCounterpartNotification");
		$key = $this->getIdentifierValue();
		$did = $this->getDebugId();
		if ($flag) {
			// Debug::print("{$f} returning true for object with key \"{$key}\" and debug ID \"{$did}\"");
		} else {
			// Debug::print("{$f} returning false object with key \"{$key}\" and debug ID \"{$did}\"");
		}
		return $this->getFlag("sendCounterpartNotification");
	}

	public function setDismissNotificationFlag(bool $value=true):bool{
		return $this->setFlag("dismissNotification", $value);
	}

	public function setSendCounterpartNotificationFlag($value){
		$f = __METHOD__;
		$print = false;
		if ($print) {
			$key = $this->getIdentifierValue();
			$did = $this->getDebugId();
			if ($value) {
				Debug::print("{$f} setting flag to true for object with key \"{$key}\" and debug ID \"{$did}\"");
			} else {
				Debug::print("{$f} setting flag to false for object with key \"{$key}\" and debug ID \"{$did}\"");
			}
		}
		return $this->setFlag("sendCounterpartNotification", $value);
	}

	/**
	 * dismiss a notification belonging to to currently authenticated user with subject $this
	 *
	 * @param mysqli $mysqli
	 * @return string
	 */
	public function dismissYourNotification($mysqli){
		$f = __METHOD__;
		try {
			$print = false;
			$note_class = $this->getNotificationClass();
			$note_type = $note_class::getNotificationTypeStatic();
			$intersection = new IntersectionData(static::getNotificationClass(), static::class, "subjectKey");
			$idn = $this->getIdentifierName();

			$st = QueryBuilder::select("hostKey")->from($intersection->getDatabaseName(), $intersection->getTableName())
				->where(new AndCommand(new WhereCondition("foreignKey", OPERATOR_EQUALS, 's'), new WhereCondition("relationship", OPERATOR_EQUALS, 's')));
			$query = RetrospectiveNotificationData::selectStatic()->where(new AndCommand(new WhereCondition("subtype", OPERATOR_EQUALS), new WhereCondition($idn, OPERATOR_IN, $this->getColumnTypeSpecifier($idn), $st)));
			$operand_notes = RetrospectiveNotificationData::loadMultiple($mysqli, $query, 'sss', $note_type, $this->getIdentifierValue(), "subjectKey");
			if (empty($operand_notes)) {
				if ($print) {
					Debug::print("{$f} nothing to dismiss");
				}
				return SUCCESS;
			}
			foreach ($operand_notes as $operand_note) {
				// $status = $operand_note->load($mysqli, $where, $args);
				$status = $operand_note->getObjectStatus();
				if ($status === ERROR_NOT_FOUND) {
					// Debug::print("{$f} object not found; this order does not have a notification");
				} elseif ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} error loading order notification from database: \"{$err}\"");
				} else {

					if (! $operand_note->hasUserData()) {
						$operand_note->loadForeignDataStructure($mysqli, "userKey");
						if (! $operand_note->hasForeignDataStructure("userKey")) {
							Debug::warning("{$f} loading user data failed");
							continue;
						}
					} elseif ($print) {
						Debug::print("{$f} user data has already been loaded");
					}

					if (! $operand_note->hasUserData()) {
						$id = $operand_note->getIdentifierValue();
						Debug::error("{$f} user data is undefined for notification with ID \"{$id}\"");
					} elseif ($print) {
						Debug::print("{$f} about to mark existing notification read");
					}
					$operand_note->setNotificationState(NOTIFICATION_STATE_DISMISSED);
					$backup = $operand_note->getPermission(DIRECTIVE_UPDATE);
					$operand_note->setPermission(DIRECTIVE_UPDATE, SUCCESS);
					$status = $operand_note->update($mysqli);
					$operand_note->setPermission(DIRECTIVE_UPDATE, $backup);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} error dismissing notification: \"{$err}\"");
					}
					// Debug::print("{$f} successfully dismissed notification");
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
