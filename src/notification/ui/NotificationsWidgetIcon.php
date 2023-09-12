<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\image\ImageElement;
use JulianSeymour\PHPWebApplicationFramework\ui\OpenAndClosableElementInterface;
use Exception;

class NotificationsWidgetIcon extends LabelElement implements OpenAndClosableElementInterface{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		try{
			parent::__construct($mode, $context);
			$this->setIdAttribute("notifications_widget_icon");
			$this->setForAttribute("widget-notifications");
			$this->addClassAttribute("notification_widget_label");
			$this->addClassAttribute("widget_icon");
			$this->setOnClickAttribute("NotificationsWidget.initializeNotificationsWidget();");
			if(Request::isXHREvent()) {
				$this->setAttribute("read_multiple", "read_multiple");
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function bindContext($context){
		$f = __METHOD__;
		try{
			$print = false;
			$ret = parent::bindContext($context);
			$bell = new ImageElement();
			$bell->setSourceAttribute("/images/Bell-05.png"); //XXX TODO replace this with a CSS drawing
			$this->appendChild($bell);
			$counter = new DivElement();
			$counter->setIdAttribute("notification_count_icon");
			$counter->addClassAttribute("notification_counter");
			$count = $context->getColumnValue("unreadNotificationCount");
			if($print) {
				Debug::print("{$f} this user's unread notification count is {$count}");
				if($count === 0) {
					Debug::print("{$f} select statement for this user is " . $context->select());
				}
			}
			$counter->setInnerHTML($count);
			if($count < 1) {
				$counter->setStyleProperty("opacity", "0");
			}
			$this->appendChild($counter);
			return $ret;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getOpenDisplayProperties(): ?array{
		return [
			"pointer-events" => "none",
			"opacity" => 0
		];
	}

	public static function getClosedDisplayProperties(): ?array{
		return [
			"pointer-events" => "auto",
			"opacity" => 1
		];
	}
}
