<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\EnabledAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationElement;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationsWidget;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationsWidgetLoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use mysqli;

class NotificationsUseCase extends UseCase implements ClientUseCaseInterface{

	use JavaScriptCounterpartTrait;

	public function getLoadoutGeneratorClass(?PlayableUser $user = null): ?string{
		return NotificationsWidgetLoadoutGenerator::class;
	}

	public function getPageContent(): ?array{
		return [
			new NotificationsWidget(ALLOCATION_MODE_LAZY, user())
		];
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	protected function getExecutePermissionClass(){
		return EnabledAccountTypePermission::class;
	}

	public function getActionAttribute(): ?string{
		return "/notifications";
	}

	public function getProcessedDataListClasses(): ?array{
		return [
			$this->getProcessedDataType()
		];
	}

	public function getConditionalElementClasses(): ?array{
		return [
			NotificationElement::class
		];
	}

	public function getConditionalDataOperandClasses(): ?array{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getConditionalProcessedFormClasses(): ?array{
		return null;
	}

	public function getProcessedDataType(): ?string{
		return DATATYPE_NOTIFICATION;
	}

	public function isCurrentUserDataOperand(): bool{
		return true;
	}

	public function getDataOperandClass(): ?string{
		return RetrospectiveNotificationData::class;
	}

	public function getResponder(int $status):?Responder{
		if($status !== SUCCESS){
			return parent::getResponder($status);
		}
		return new NotificationsResponder();
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData{
		return user();
	}

	public function getClientUseCaseName(): ?string{
		return "notifications";
	}
}
