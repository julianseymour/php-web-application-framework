<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\pin;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\GhostInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use Exception;

abstract class SortNotificationForm extends AjaxForm implements TemplateElementInterface
{

	protected abstract static function getButtonValueAttributeStatic();

	public function __construct($mode = ALLOCATION_MODE_NEVER, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("sort_notification_form");
	}

	protected static function getWidgetName(): string
	{
		return "notification";
	}

	public static function getTemplateContextClass(): string
	{
		return RetrospectiveNotificationData::class;
	}

	public static function isTemplateworthy(): bool
	{
		return true;
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		$value = static::getButtonValueAttributeStatic();
		return "{$value}_notification";
	}

	public function setIdAttribute($id)
	{
		$f = __METHOD__; //SortNotificationForm::getShortClass()."(".static::getShortClass().")->setIdAttribute()";
		if(is_string($id) && $id === "pin_notification_form") {
			Debug::error("{$f} uhoh");
		}
		return parent::setIdAttribute($id);
	}

	public function bindContext($context)
	{
		$suffix = $context->getIdAttributeSuffixCommand();
		$widget = $this->getWidgetName();
		$value = static::getButtonValueAttributeStatic();
		$this->setIdAttribute(new ConcatenateCommand("{$value}_", $widget, "-", $suffix));
		$this->setAttribute('uniqueKey', $suffix);
		$this->addClassAttribute(new ConcatenateCommand("{$value}_", $widget, "_form"));
		$this->setAttribute("note_type", new GetColumnValueCommand($context, "subtype")); // ->getColumnValueCommand("subtype"));
		$ret = parent::bindContext($context);
		return $ret;
	}

	public static function getMethodAttributeStatic(): ?string
	{
		return HTTP_REQUEST_METHOD_POST;
	}

	public static function getErrorCallbackStatic(): ?string
	{
		return "NotificationData.restorePinnedNotification";
	}

	public function getFormDataIndices(): ?array
	{
		return [
			"pinnedTimestamp" => GhostInput::class
		];
	}

	public function getAdHocInputs(): ?array
	{
		$f = __METHOD__; //SortNotificationForm::getShortClass()."(".static::getShortClass().")->getAdHocFormInputs()";
		try{
			$inputs = parent::getAdHocInputs();
			$widget = new HiddenInput($this->getAllocationMode());
			$widget->setNameAttribute("widget");
			$wsn = $this->getWidgetName();
			$widget->setValueAttribute($wsn);
			$inputs[$widget->getNameAttribute()] = $widget;
			return $inputs;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function generateButtons(string $name): ?array
	{
		$context = $this->getContext();
		$button = $this->generateGenericButton($name, static::getButtonValueAttributeStatic());
		$button->setInnerHTML(static::getLabelInnerHTML());
		$onclick = "NotificationData.reinsertNotification(event, this)";
		$button->setOnClickAttribute($onclick);
		$button->setAttribute('uniqueKey', $context->getIdAttributeSuffixCommand());
		$button->setAttribute("note_type", new GetColumnValueCommand($context, "subtype")); // ->getColumnValueCommand("subtype"));
		return [
			$button
		];
	}
}
