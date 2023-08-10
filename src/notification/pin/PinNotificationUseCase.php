<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\pin;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\EnabledAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationOptionsElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use mysqli;

class PinNotificationUseCase extends InteractiveUseCase
{

	public function getProcessedDataListClasses(): ?array
	{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getConditionalElementClasses(): ?array
	{
		return [
			$this->getProcessedDataType() => NotificationOptionsElement::class
		];
	}

	public function getConditionalDataOperandClasses(): ?array
	{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getConditionalProcessedFormClasses(): ?array
	{
		return [
			PinNotificationForm::class
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

	public function getProcessedFormClass(): ?string
	{
		return PinNotificationForm::class;
	}

	public function getDataOperandClass(): ?string
	{
		return RetrospectiveNotificationData::class;
	}

	protected function getExecutePermissionClass()
	{
		return EnabledAccountTypePermission::class;
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData
	{
		return user();
	}

	public static function getNotificationTargetId()
	{
		return "pin_notification_here";
	}

	public function getResponder(): ?Responder
	{
		return new PinNotificationResponder();
	}

	public function getUseCaseId()
	{
		return USE_CASE_PIN_NOTIFICATION;
	}

	public function getActionAttribute(): ?string
	{
		return "/pin";
	}

	public function isCurrentUserDataOperand(): bool
	{
		return true;
	}
}
