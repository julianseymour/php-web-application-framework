<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\app\EmptyModule;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationsWidget;

class NotificationModule extends EmptyModule{

	public function getWidgetClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationsWidget::class
		];
	}

	public function getClientRenderedFormClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissNotificationForm::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\pin\PinNotificationForm::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\pin\UnpinNotificationForm::class
		];
	}

	public function getTemplateElementClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissNotificationForm::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationOptionsElement::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\pin\PinNotificationForm::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\pin\RepinNotificationForm::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\pin\UnpinNotificationForm::class
		];
	}

	public function getFormDataSubmissionClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissNotificationForm::class
		];
	}

	public function getUseCaseDictionary(): ?array{
		return [
			"dismiss" => \JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissNotificationUseCase::class,
			"dismiss_all" => \JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissAllNotificationsUseCase::class,
			//"fetch_update" => \JulianSeymour\PHPWebApplicationFramework\notification\push\FetchNotificationUseCase::class,
			"notifications" => \JulianSeymour\PHPWebApplicationFramework\notification\NotificationsUseCase::class,
			"pin" => \JulianSeymour\PHPWebApplicationFramework\notification\pin\PinNotificationUseCase::class,
			"subscribe" => \JulianSeymour\PHPWebApplicationFramework\notification\push\SavePushSubscriptionUseCase::class,
			"unpin" => \JulianSeymour\PHPWebApplicationFramework\notification\pin\UnpinNotificationUseCase::class,
			"unsubscribe" => \JulianSeymour\PHPWebApplicationFramework\notification\push\UnsubscribePushNotificationsUseCase::class
		];
	}

	public function getJavaScriptFilePaths(): ?array{
		return [
			FRAMEWORK_INSTALL_DIRECTORY . "/notification/ui/NotificationsWidget.js",
			//FRAMEWORK_INSTALL_DIRECTORY . "/notification/push/push.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/notification/dismiss/DismissAllNotificationsUseCase.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/notification/NotificationsUseCase.js"
		];
	}

	public function getClientUseCaseDictionary(): ?array{
		return [
			"dismiss_all" => "DismissAllNotificationsUseCase",
			"notifications" => "NotificationsUseCase"
		];
	}

	public function getInvokeableJavaScriptFunctions(): ?array{
		return [
			"beep" => "playNotificationSound",
			"playNotificationSound" => "playNotificationSound"
		];
	}

	public function getMessageEventHandlerCases(): ?array{
		$break = CommandBuilder::break();
		// $info = new JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand("response.info");
		return [
			"beep" => [
				CommandBuilder::call("beep"),
				$break
			]
		];
	}

	public function getPollingUseCaseClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\notification\recent\RecentNotificationsUseCase::class
		];
	}
	
	public function getCascadingStyleSheetFilePaths(): ?array{
		return [
			FRAMEWORK_INSTALL_DIRECTORY . "/notification/style-notif.css",
			\JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationsWidget::getStyleSheetPath()
		];
	}
	
	public function getEmbedName():string{
		return "notification";
	}
}
