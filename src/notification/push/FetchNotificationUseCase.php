<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\EnabledAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use Exception;
use mysqli;

class FetchNotificationUseCase extends InteractiveUseCase
{

	public function execute(): int
	{
		$f = __METHOD__; //FetchNotificationUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$print = false;
			if ($print) {
				$name = user()->getName();
				Debug::print("{$f} username is \"{$name}\"");
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			$arr_cipher_64 = getInputParameters();
			if ($print) {
				Debug::print("{$f} about to print base 64 encoded cipher array");
				Debug::printArray($arr_cipher_64);
			}
			$num = user()->decrypt(base64_decode($arr_cipher_64['num_cipher_64'])); // ,
			$user_key = user()->decrypt(base64_decode($arr_cipher_64['user_key_cipher_64'])); // ,
			if ($user_key == null || $user_key == "") {
				Debug::error("{$f} user key is null or empty string");
			} elseif ($print) {
				Debug::print("{$f} client key is \"{$user_key}\"");
			}

			$key = user()->getIdentifierValue();
			if ($user_key !== $key) {
				Debug::error("{$f} looks like you logged in on a public computer -- need to reassign notification with user key \"{$user_key}\" so it has user key \"{$key}\"");
				return $this->setObjectStatus(ERROR_EXPIRED_PUSH_SUBSCRIPTION);
			}

			if (! isset($num)) {
				Debug::warning("{$f} object number is undefined");
				return $this->setObjectStatus(ERROR_NULL_OBJECTNUM);
			} elseif ($print) {
				Debug::print("{$f} about to fetch notification \"{$num}\"");
			}
			$note = new RetrospectiveNotificationData();
			$note->setUserData(user());
			$note->load($mysqli, "num", $num);
			$status = $note->getObjectStatus();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} object has error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$status = $note->loadForeignDataStructures($mysqli, false, 3);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loadForeignDataStructures returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$status = Loadout::expandForeignDataStructures($note, $mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f}} expandTree returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			if ($print && $note->getNotificationType() === NOTIFICATION_TYPE_MESSAGE) {
				$target = $note->getSubjectData();
				if (! $target->hasMessageType()) {
					$class = $target->getClass();
					Debug::error("{$f} target object of class \"{$class}\" lacks a message type");
				}
				$str = $target->getMessageTypeString();
				Debug::print("{$f} target message is subtype \"{$str}\"");
			}
			$this->setDataOperandObject($note);
			// $note->configureArrayMembership("push");
			// app()->getResponse($this)->pushDataStructure($note);
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return $this->setObjectStatus(SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function getExecutePermissionClass()
	{
		return EnabledAccountTypePermission::class;
	}

	public function getDataOperandClass(): ?string
	{
		return RetrospectiveNotificationData::class;
	}

	public function getConditionalDataOperandClasses(): ?array
	{
		return [
			RetrospectiveNotificationData::class
		];
	}

	public function getProcessedDataType(): ?string
	{
		return DATATYPE_NOTIFICATION;
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return false;
	}

	public function getUseCaseId()
	{
		return USE_CASE_FETCH_NOTIFICATION;
	}

	public function getActionAttribute(): ?string
	{
		return "/fetch_update";
	}

	public function getProcessedDataListClasses(): ?array
	{
		$f = __METHOD__; //FetchNotificationUseCase::getShortClass()."(".static::getShortClass().")::getProcessedDataListClasses()";
		ErrorMessage::unimplemented($f);
	}

	public function getConditionalElementClasses(): ?array
	{
		$f = __METHOD__; //FetchNotificationUseCase::getShortClass()."(".static::getShortClass().")::getConditionalElementClasses()";
		ErrorMessage::unimplemented($f);
	}

	public function getConditionalProcessedFormClasses(): ?array
	{
		$f = __METHOD__; //FetchNotificationUseCase::getShortClass()."(".static::getShortClass().")::getConditionalProcessedFormClasses()";
		Debug::printArray(getInputParameters());
		ErrorMessage::unimplemented($f);
	}

	public function isCurrentUserDataOperand(): bool
	{
		return true;
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData
	{
		return user();
	}

	public function getResponder(): ?Responder
	{
		if ($this->getObjectStatus() !== SUCCESS) {
			return parent::getResponder();
		}
		return new FetchNotificationResponder();
	}
}

