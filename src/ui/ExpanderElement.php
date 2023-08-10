<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use Exception;

class ExpanderElement extends Element
{

	protected $collapseId;

	protected $collapseLabelStatus;

	protected $collapseLabelString;

	protected $estimatedHeight;

	protected $expanderContentsDefined;

	protected $expandId;

	protected $expandLabelString;

	protected $expandingContainer;

	protected $triggerInput;

	protected $triggerInputChecked;

	// = false;
	protected $triggerInputName;

	protected $triggerInputType;

	protected function afterConstructorHook(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null): int
	{
		$ret = parent::afterConstructorHook($mode, $context);
		$this->addClassAttribute("expander");
		$this->setCollapseLabelStatus(true);
		$this->setExpanderContentsDefined(false);
		return $ret;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->collapseId);
		unset($this->collapseLabelStatus);
		unset($this->collapseLabelString);
		unset($this->estimatedHeight);
		unset($this->expandId);
		unset($this->expandingContainer);
		unset($this->expandLabelString);
		unset($this->expanderContentsDefined);
		unset($this->triggerInput);
		unset($this->triggerInputChecked);
		unset($this->triggerInputName);
		unset($this->triggerInputType);
	}

	public function setTriggerInputNameAttribute($name)
	{
		return $this->triggerInputName = $name;
	}

	public function hasTriggerInputType()
	{
		return isset($this->triggerInputType);
	}

	public function getTriggerInputType()
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->getTriggerInputType()";
		if (! $this->hasTriggerInputType()) {
			Debug::error("{$f} trigger input type is undefined");
		}
		return $this->triggerInputType;
	}

	public function setTriggerInputChecked($checked)
	{
		return $this->triggerInputChecked = $checked;
	}

	public function setTriggerInputType($type)
	{
		return $this->triggerInputType = $type;
	}

	public function hasExpandLabelString()
	{
		return isset($this->expandLabelString);
	}

	public function getExpandLabelString()
	{
		if (! $this->hasExpandLabelString()) {
			return _("Expand");
		}
		return $this->expandLabelString;
	}

	public function setExpandLabelString($s)
	{
		return $this->expandLabelString = $s;
	}

	public function hasCollapseLabelString()
	{
		return isset($this->collapseLabelString);
	}

	public function getCollapseLabelString()
	{
		if (! $this->hasCollapseLabelString()) {
			return _("Collapse");
		}
		return $this->collapseLabelString;
	}

	public function setCollapseLabelString($s)
	{
		return $this->collapseLabelString = $s;
	}

	public function getTriggerInputClass()
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->getTriggerInputClass()";
		try {
			$type = $this->getTriggerInputType();
			switch ($type) {
				case INPUT_TYPE_CHECKBOX:
					return CheckboxInput::class;
				case INPUT_TYPE_RADIO:
					return RadioButtonInput::class;
				default:
					Debug::error("{$f} invalid form input type \"{$type}\"");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasExpandTriggerInputIdAttribute()
	{
		return isset($this->expandId);
	}

	public function getExpandTriggerInputIdAttribute()
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->getExpandTriggerInputIdAttribute()";
		if (! $this->hasExpandTriggerInputIdAttribute()) {
			Debug::error("{$f} expand trigger input ID attribute is undefined");
		}
		return $this->expandId;
	}

	public function setExpandTriggerInputIdAttribute($id)
	{
		/*
		 * $f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->setExpandTriggerInputIdAttribute({$id})";
		 * Debug::print("{$f} entered");
		 * if($id instanceof Command){
		 * if($id->getFlag("reserved")){
		 * Debug::error("{$f} command was already reserved");
		 * }
		 * $id->setFlag("reserved");
		 * }
		 */
		return $this->expandId = $id;
	}

	public function hasCollapseTriggerInputIdAttribute()
	{
		return isset($this->collapseId);
	}

	public function getCollapseTriggerInputIdAttribute()
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->getCollapseTriggerInputIdAttribute()";
		try {
			$type = $this->getTriggerInputType();
			switch ($type) {
				case INPUT_TYPE_CHECKBOX:
					return $this->getExpandTriggerInputIdAttribute();
				case INPUT_TYPE_RADIO:
					if (! $this->hasCollapseTriggerInputIdAttribute()) {
						Debug::error("{$f} collapse trigger ID attribute is undefined");
					}
					return $this->collapseId;
				default:
					Debug::error("{$f} invalid form input type \"{$type}\"");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setCollapseTriggerInputIdAttribute($id)
	{
		return $this->collapseId = $id;
	}

	public function setCollapseLabelStatus($s)
	{
		return $this->collapseLabelStatus = $s;
	}

	public function getCollapseLabelStatus()
	{
		return $this->collapseLabelStatus;
	}

	public function hasTriggerInputNameAttribute()
	{
		return isset($this->triggerInputName);
	}

	public function getTriggerInputNameAttribute()
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->getTriggerInputNameAttribute()";
		if (! $this->hasTriggerInputNameAttribute()) {
			Debug::error("{$f} trigger input name attribute is undefined");
		}
		return $this->triggerInputName;
	}

	public function hasTriggerInput()
	{
		return isset($this->triggerInput);
	}

	public function setExpanderContentsDefined($defined)
	{
		return $this->expanderContentsDefined = $defined;
	}

	public function getExpanderContentsDefined()
	{
		return $this->expanderContentsDefined === true;
	}

	public function expand()
	{
		$this->getTriggerInput()->check();
		return $this;
	}

	public function collapse()
	{
		$input = $this->getTriggerInput();
		if ($input->hasCheckedAttribute()) {
			$input->removeCheckedAttribute();
		}
		return $this;
	}

	public function getTriggerInput()
	{
		if ($this->hasTriggerInput()) {
			return $this->triggerInput;
		}
		$mode = $this->getAllocationMode();
		$tic = $this->getTriggerInputClass();
		$input = new $tic($mode);
		$input->addClassAttribute("hidden");
		if ($this->getTriggerInputType() === INPUT_TYPE_RADIO) {
			$input->setNameAttribute($this->getTriggerInputNameAttribute());
		}
		$input->setIdAttribute($this->getExpandTriggerInputIdAttribute());
		$input->setNoUpdateFlag(true);
		return $this->triggerInput = $input;
	}

	protected function generatePredecessors(): ?array
	{
		return [
			$this->getTriggerInput()
		];
	}

	protected function createLabelContainer()
	{
		$mode = $this->getAllocationMode();
		$label_container = new DivElement($mode);
		$label_container->addClassAttribute("label_container");
		$open_label = $this->createExpandLabelElement();
		$label_container->appendChild($open_label);
		if ($this->getCollapseLabelStatus()) {
			$close_label = $this->createCollapseLabelElement();
			$label_container->appendChild($close_label);
		}
		return $label_container;
	}

	protected function createExpandLabelElement()
	{
		$mode = $this->getAllocationMode();
		$open_label = new LabelElement($mode);
		$open_label->addClassAttribute("expand_label");
		$open_label->setInnerHTML($this->getExpandLabelString());
		$for = $this->getExpandTriggerInputIdAttribute();
		$open_label->setForAttribute($for);
		$id = new ConcatenateCommand("expand_label-", $for);
		$open_label->setIdAttribute($id);
		return $open_label;
	}

	public function createCollapseLabelElement()
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->createCollapseLabelElement()";
		$print = false;
		$mode = $this->getAllocationMode();
		if ($print) {
			Debug::print("{$f} child generation mode is \"{$mode}\"");
		}
		$close_label = new LabelElement($mode);
		$close_label->addClassAttribute("collapse_label");
		$close_label->setInnerHTML($this->getCollapseLabelString());
		$for = $this->getCollapseTriggerInputIdAttribute();
		if ($this->getTriggerInputType() === INPUT_TYPE_CHECKBOX) {
			if (is_object($for)) {
				if ($print) {
					$idc = $for->getClass();
					Debug::print("{$f} this is a checkbox-based expander -- about to replicate command of class \"{$idc}\"");
				}
				$for = $for->replicate();
				$id = new ConcatenateCommand("collapse_label-", $for->replicate());
			} else {
				$id = new ConcatenateCommand("collapse_label-", $for);
			}
		} else {
			$id = new ConcatenateCommand("collapse_label-", $for);
		}
		$close_label->setForAttribute($for);
		$close_label->setIdAttribute($id);
		return $close_label;
	}

	public function hasEstimatedHeight(): bool
	{
		return isset($this->estimatedHeight);
	}

	public function getEstimatedHeight(): string
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->getEstimatedHeight()";
		if (! $this->hasEstimatedHeight()) {
			Debug::error("{$f} estimated height is undefined");
		}
		return $this->estimatedHeight;
	}

	public function setEstimatedHeight(?string $height): ?string
	{
		if ($height == null) {
			unset($this->estimatedHeight);
			return null;
		}
		return $this->estimatedHeight = $height;
	}

	protected function createExpandingContainer()
	{
		if ($this->hasExpandingContainer()) {
			return $this->expandingContainer;
		}
		$mode = $this->getAllocationMode();
		$expandme = new DivElement($mode);
		$expandme->addClassAttribute("expand_me");
		if ($this->hasEstimatedHeight()) {
			$expandme->setStyleProperty("max-height", $this->getEstimatedHeight());
			$expandme->setStyleProperty("overflow", "auto");
		}
		return $this->expandingContainer = $expandme;
	}

	public function hasExpandingContainer()
	{
		return isset($this->expandingContainer);
	}

	public function setExpanderContents($contents)
	{
		$f = __METHOD__; //ExpanderElement::getShortClass()."(".static::getShortClass().")->setExpanderContents()";
		try {
			$label_container = $this->createLabelContainer();
			$this->appendChild($label_container);
			$expandme = $this->createExpandingContainer();
			if (is_array($contents)) {
				foreach ($contents as $c) {
					if ($c instanceof Command) {
						$expandme->resolveTemplateCommand($c);
					} else {
						$expandme->appendChild($c);
					}
				}
			} elseif ($contents instanceof Command) {
				$expandme->resolveTemplateCommand($contents);
			} else {
				$expandme->appendChild($contents);
			}
			$this->appendChild($expandme);
			$this->setExpanderContentsDefined(true);
			return $contents;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
