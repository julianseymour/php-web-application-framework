<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\ScriptElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\Choice;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\notification\pin\PinNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\notification\pin\RepinNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\notification\pin\UnpinNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\ui\CloseMenuLabel;
use JulianSeymour\PHPWebApplicationFramework\ui\CounterTab;
use JulianSeymour\PHPWebApplicationFramework\ui\TabMutex;
use JulianSeymour\PHPWebApplicationFramework\ui\WidgetInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class NotificationsWidget extends DivElement implements JavaScriptCounterpartInterface, WidgetInterface{

	use JavaScriptCounterpartTrait;
	use StyleSheetPathTrait;
	
	protected $widgetIcon;

	protected $tabMutex;

	public static function meetsDisplayCriteria(?UseCase $use_case): bool{
		$user = user();
		return $user->isEnabled() && ! $user instanceof AnonymousUser;
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("notification_list_container");
	}

	public function getTemplateScriptContainer(){
		$f = __METHOD__;
		try{

			$print = false;

			$hidden = new ScriptElement();
			$hidden->setIdAttribute("notification_template_scripts");
			$hidden->addClassAttribute("hidden");
			$dummy = new RetrospectiveNotificationData();
			$dummy->setNotificationType(NOTIFICATION_TYPE_TEMPLATE);

			$pin = new PinNotificationForm(ALLOCATION_MODE_TEMPLATE);
			$pin->bindContext($dummy);
			$hidden->appendChild($pin->generateTemplateFunction());

			$repin = new RepinNotificationForm(ALLOCATION_MODE_TEMPLATE);
			$repin->bindContext($dummy);
			$hidden->appendChild($repin->generateTemplateFunction());

			$unpin = new UnpinNotificationForm(ALLOCATION_MODE_TEMPLATE);
			$unpin->bindContext($dummy);
			$hidden->appendChild($unpin->generateTemplateFunction());

			$dismiss = new DismissNotificationForm(ALLOCATION_MODE_TEMPLATE);
			$dismiss->bindContext($dummy);
			$hidden->appendChild($dismiss->generateTemplateFunction());

			foreach(mods()->getTypedNotificationClasses() as $nc) {
				if($print) {
					Debug::print("{$f} about to generate a template function for notifications of class \"{$nc}\"");
				}
				$dummy = new RetrospectiveNotificationData();
				$dummy->setNotificationType($nc::getNotificationTypeStatic());
				$ec = $nc::getElementClassStatic();
				$e = new $ec(ALLOCATION_MODE_TEMPLATE);
				$e->bindContext($dummy);
				$hidden->appendChild($e->generateTemplateFunction());
			}
			return $hidden;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$context = $this->getContext();
			$use_case = app()->getUseCase();
			$mode = $this->getAllocationMode();
			$header = new DivElement($mode);
			$header->addClassAttribute("notifications_header", "background_color_1");
			$header_span = new SpanElement($mode);
			$header_span->setInnerHTML(_("Notifications"));
			$header->appendChild($header_span);
			$close_label = new CloseMenuLabel($mode, $context);
			$close_label->setForAttribute("widget-none");
			$header->appendChild($close_label);
			$this->appendChild($header);
			$hidden = $this->getTemplateScriptContainer($use_case);
			$this->appendChild($hidden);
			$mutex = $this->getTabMutex();
			$notification_filter_tabs = new DivElement();
			$notification_filter_tabs->addClassAttribute("tab_labels", "background_color_4");
			$notification_filter_tabs->setStyleProperties([
				"height" => "56px",
				"display" => "block",
				"overflow-x" => "auto",
				"white-space" => "nowrap"
			]);
			$notification_filter_tabs->appendChild(...array_values($mutex->getLabelElements()));
			$this->appendChild($mutex->getDynamicStyleElement(), $mutex, $notification_filter_tabs);
			$notification_list = new NotificationListElement($mode, $context);
			$single_notification_options = new RadioButtonInput($mode);
			$single_notification_options->addClassAttribute("hidden");
			$single_notification_options->setNameAttribute("single_notification_options");
			$single_notification_options->setIdAttribute("notification_options-none");
			$this->appendChild($single_notification_options, $notification_list);
			return $this->getChildNodes();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function getTabMutex(): TabMutex{
		if(isset($this->tabMutex) && $this->tabMutex instanceof TabMutex) {
			return $this->tabMutex;
		}
		$mutex = new TabMutex();
		$mutex->setNameAttribute("notification_filter");
		$mutex->setLabelElementClass(CounterTab::class);
		$mutex->hide();
		$all = new Choice(CONST_ALL, _("All"), true);
		$all->setAllFlag(true);
		$options = [
			$all
		];
		foreach(mods()->getTypedNotificationClasses() as $type => $class) {
			if($type === NOTIFICATION_TYPE_TEST) {
				continue;
			}
			$option = new Choice($type, $class::getNotificationTypeString(null));
			$options[$type] = $option;
		}
		$mutex->setChoices($options);
		return $this->tabMutex = $mutex;
	}

	public static function getIdAttributeStatic(): ?string{
		return "notifications_widget_container";
	}

	public static function getWidgetLabelId(){
		return "notifications_widget_icon";
	}

	public static function getWidgetName(): string{
		return "notifications";
	}

	public static function getOpenDisplayProperties(): ?array{
		return [
			"max-height" => "calc(100% - 50px)",
			"max-width" => "480px",
			"transform" => "scale3d(1, 1, 1)",
			"border-radius" => "0",
			"pointer-events" => "auto",
			"opacity" => "1"
		];
	}

	public static function getClosedDisplayProperties(): ?array{
		return [
			"max-width" => "480px",
			"transform" => "scale3d(0, 0, 0)",
			"border-radius" => "0.5rem",
			"opacity" => "0"
		];
	}

	public static function getIconClass($context = null): ?string{
		return NotificationsWidgetIcon::class;
	}

	public static function getLoadoutGeneratorClassStatic(): ?string{
		if(! Request::isAjaxRequest()) {
			return null;
		}
		return NotificationsWidgetLoadoutGenerator::class;
	}
}
