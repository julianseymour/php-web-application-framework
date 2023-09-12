<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\common\IteratorTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;

class WidgetContainer extends DivElement{

	use IteratorTrait;

	protected $widgetClass;

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("widget_container");
	}

	public function setWidgetClass(?string $widgetClass): ?string{
		$f = __METHOD__;
		if($widgetClass == null) {
			unset($this->widgetClass);
			return null;
		}elseif(!is_string($widgetClass) || ! class_exists($widgetClass) || ! is_a($widgetClass, WidgetInterface::class, true)) {
			Debug::error("{$f} class \"{$widgetClass}\" is not a WidgetInterface");
		}
		$name = $widgetClass::getWidgetName();
		$this->setAttribute("widget", $name);
		$cid = "{$name}_widget_container";
		$this->setIdAttribute($cid);
		return $this->widgetClass = $widgetClass;
	}

	public function hasWidgetClass(): bool{
		return isset($this->widgetClass);
	}

	public function getWidgetClass(): string{
		$f = __METHOD__;
		if(!$this->hasWidgetClass()) {
			Debug::error("{$f} widget class is undefined");
		}
		return $this->widgetClass;
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		$mode = $this->getAllocationMode();
		$context = $this->getContext();
		$wc = $this->getWidgetClass();
		$name = $wc::getWidgetName();
		$icon_class = $wc::getIconClass($context);
		if($icon_class !== null) {
			$icon = new $icon_class($mode, $context);
			if(!$icon->hasIdAttribute()) {
				$icon->setIdAttribute("{$name}_widget_icon");
			}
			$icon->addClassAttribute("widget_icon");
			$tr = $this->getIterator() * 50;
			$icon->setStyleProperties([
				"transform" => "translate({$tr}px, calc(100% - 50px))"
			]);
			$this->appendChild($icon);
		}
		$widget = new $wc($mode, $context);
		if(!$widget->hasIdAttribute()) {
			$widget->setIdAttribute("{$name}_widget");
		}
		$this->appendChild($widget);
		return $this->getChildNodes();
	}
}
