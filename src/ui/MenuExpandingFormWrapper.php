<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\ExpandingFormWrapperElement;
use Exception;

class MenuExpandingFormWrapper extends ExpandingFormWrapperElement
{

	protected $nestedFormClass;

	public function setNestedFormClass($form_class)
	{
		return $this->nestedFormClass = $form_class;
	}

	public function hasNestedFormClass()
	{
		return isset($this->nestedFormClass);
	}

	public function getNestedFormClass()
	{
		$f = __METHOD__; //MenuExpandingFormWrapper::getShortClass()."(".static::getShortClass().")->getNestedFormClass()";
		try{
			if(!$this->hasNestedFormClass()) {
				Debug::error("{$f} nested form class is undefined");
			}
			return $this->nestedFormClass;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasTriggerInputType()
	{
		return true;
	}

	public function hasTriggerInputNameAttribute()
	{
		return true;
	}

	public function getTriggerInputType()
	{
		return INPUT_TYPE_RADIO;
	}

	public function getTriggerInputNameAttribute()
	{
		return "radio_settings"; // XXX will need to change for this to work with device names &c
	}

	public function bindContext($context)
	{
		$form_class = $this->getNestedFormClass();
		$label_string = $form_class::getExpandingMenuLabelString($context);
		$radio_id = $form_class::getExpandingMenuRadioButtonIdAttribute();
		$this->setExpandLabelString($label_string);
		$this->setCollapseLabelString($label_string);
		$this->setExpandTriggerInputIdAttribute($radio_id);
		return parent::bindContext($context);
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //MenuExpandingFormWrapper::getShortClass()."(".static::getShortClass().")::generateChildNodes()";
		try{
			$context = $this->getContext();
			$mode = $this->getAllocationMode();
			$form_class = $this->getNestedFormClass();
			$context = $this->getContext();
			$form = new $form_class($mode, $context);
			$this->setExpanderContents($form);
			return $this->getChildNodes();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("expand_container");
		$this->addClassAttribute("background_color_1");
		$this->setElementTag("div");
	}

	protected function createExpandLabelElement()
	{
		$label = parent::createExpandLabelElement();
		$label->addClassAttribute("expand_select");
		return $label;
	}

	public function createCollapseLabelElement()
	{
		$label = parent::createCollapseLabelElement();
		$label->addClassAttribute("expand_deselect");
		return $label;
	}

	public function createExpandedContentElement()
	{
		$element = parent::createExpandedContentElement();
		$maxheight = $this->getNestedFormClass()::getMaxHeightRequirement();
		$element->setStyleProperty("max-height", $maxheight);
		return $element;
	}

	public function getCollapseTriggerInputIdAttribute()
	{
		return "radio_settings_none";
	}
}
