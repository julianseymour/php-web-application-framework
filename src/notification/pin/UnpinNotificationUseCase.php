<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\pin;

class UnpinNotificationUseCase extends PinNotificationUseCase{

	public function getProcessedFormClass(): ?string{
		return UnpinNotificationForm::class;
	}

	public static function getNotificationTargetId(){
		return "insert_notification_here";
	}

	public function getActionAttribute(): ?string{
		return "/unpin";
	}
}
