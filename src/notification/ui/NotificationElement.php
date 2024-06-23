<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AddClassCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\BindElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\DateStringCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\TimeStringCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationData;
use Exception;

abstract class NotificationElement extends DivElement{

	public abstract function getNotificationContent();

	public abstract function getNotificationPreview();

	public abstract function getNotificationTitle();

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		try{
			$this->setIdOverride("note");
			parent::__construct($mode, $context);
			$this->addClassAttribute("notification");
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected static function getNotificationOptionsElementClass(): string{
		return NotificationOptionsElement::class;
	}

	public function getIndividualNotificationForms(){
		$context = $this->getContext();
		$mode = $this->getAllocationMode();
		$options_class = $this->getNotificationOptionsElementClass();
		$options = new $options_class($mode);
		$options->setIdOverride("options");
		$options->setParentNode($this);
		$options_bound = new AppendChildCommand($this, new BindElementCommand($options, $context));
		return [
			$this->getNotificationDateTimeDismissalContainer(),
			$options_bound
		];
	}

	public function getNotificationDateTime(){
		$f = __METHOD__;
		$mode = $this->getAllocationMode();
		$notification_datetime = new SpanElement();
		$notification_datetime->addClassAttribute("notification_datetime");
		$date = new DivElement();
		$date->setAllocationMode($mode);
		$date->addClassAttribute("date");
		$context = $this->getContext();
		$date->setInnerHTML(
			new DateStringCommand(new GetColumnValueCommand($context, "updatedTimestamp"))
		);
		$notification_datetime->appendChild($date);
		$time = new DivElement();
		$time->setAllocationMode($mode);
		$time->addClassAttribute("time");
		$time->setInnerHTML(
			new TimeStringCommand(new GetColumnValueCommand($context, "updatedTimestamp"))
		);
		$notification_datetime->appendChild($time);
		return $notification_datetime;
	}

	public function getNotificationDateTimeDismissalContainer(){
		return $this->getNotificationDateTime();
	}

	public static function getIdSuffixName(){
		return NotificationData::getIdentifierNameStatic();
	}

	public static function getIdAttributeStatic($context){
		$f = __METHOD__;
		$print = false;
		$ret = new ConcatenateCommand("notification-", new NotificationIdSuffixCommand($context));
		if($print){
			$did = $ret->getDebugId();
			Debug::print("{$f} entered, debug ID id {$did}");
		}
		return $ret;
	}

	public function bindContext($context){
		$f = __METHOD__;
		$context = parent::bindContext($context);
		$this->setAttribute("count", new GetColumnValueCommand($context, "notificationCount"));
		$this->setIdAttribute($this->getIdAttributeStatic($context));
		if(!$this->hasAttribute("tab")){
			$subtype = new GetColumnValueCommand($context, "subtype");
			$this->setAttribute("tab", $subtype);
		}
		$dismissable = new AddClassCommand();
		$dismissable->setElement($this);
		$dismissable->setClassNames(["dismissable"]);
		$this->resolveTemplateCommand(
			IfCommand::hasColumnValue($context, "dismissable")->then(
				$dismissable
			)
		);
		return $context;
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$notification_content = $this->getNotificationContent();
			$this->appendChild($notification_content);
			$forms = $this->getIndividualNotificationForms();
			$this->appendChild(...$forms);
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getNotificationOptions(){
		$f = __METHOD__;
		$options = [
			'body' => $this->getNotificationPreview(),
			'icon' => '/images/smiley.png',
			'badge' => '/images/smiley.png',
			'tag' => $this->getIdAttribute()
		];
		return $options;
	}

	public static function isTemplateworthy(): bool{
		return true;
	}
}
