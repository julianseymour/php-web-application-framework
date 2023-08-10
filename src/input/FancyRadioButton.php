<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class FancyRadioButton extends RadioButtonInput
{

	use StyleSheetPathTrait;
	
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		// $f = __METHOD__; //FancyRadioButton::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($mode, $context);
		$this->addClassAttribute("hidden");
	}

	public function getLabelElement(): LabelElement
	{
		if ($this->hasLabelElement()) {
			return parent::getLabelElement();
		}
		$mode = $this->getAllocationMode();
		$label = new LabelElement($mode);
		$label->setForAttribute($this->getIdAttribute());
		$label->addClassAttribute("fancy_radio_button");
		$label->setAllowEmptyInnerHTML(true);
		if ($this->hasLabelString()) {
			$span = new SpanElement($mode);
			$span->setInnerHTML($this->getLabelString());
			$label->pushSuccessor($span);
		}
		return $this->labelElement = $label;
	}

	protected function generateSuccessors(): ?array
	{
		$label = $this->getLabelElement();
		if($this->hasStyleProperties()){
			$label->setStyleProperties($this->ejectStyleProperties());
		}
		return [
			$label
		];
	}
}
