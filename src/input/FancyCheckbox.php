<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

class FancyCheckbox extends CheckboxInput
{

	use StyleSheetPathTrait;
	
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("hidden", "fancy_checkbox");
	}

	public function getLabelElement(): LabelElement{
		$f = __METHOD__;
		$print = false;
		if ($this->hasLabelElement()) {
			if ($print) {
				Debug::print("{$f} label already existed");
			}
			if ($this->labelElement->getDeletedFlag()) {
				Debug::error("{$f} label was already deleted");
			} elseif (! $this->labelElement->hasAttributes()) {
				Debug::error("{$f} label lacks any attributes whatsoever");
			} elseif ($print) {
				Debug::print("{$f} returning normally");
			}
			return $this->labelElement;
		} elseif (! $this->hasIdAttribute()) {
			if($this->hasForm()){
				$form = $this->getForm();
				if($print){
					Debug::print("{$f} form class is ".$form->getShortClass().", declared ".$form->getDeclarationLine());
				}
				if($form->hasIdAttribute() && $this->getNameAttribute()){
					$this->setIdAttribute(new concatenateCommand($form->getIdAttribute(), "-", $this->getNameAttribute()));
				}else{
					Debug::print("{$f} either form lacks an ID attribute, or this lacks a name attribute");
				}
			}else{
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} this element cannot be rendered without an ID attribute or a form with an ID attribute and a name attribute. Declared {$decl}");
			}
		}
		if ($print) {
			$decl = $this->getDeclarationLine();
			Debug::printStackTraceNoExit("{$f} declared {$decl}");
		}
		$label = new LabelElement($this->getAllocationMode());
		$label->addClassAttribute("fancy_checkbox_label");
		$label->setForAttribute($this->getIdAttribute());
		$label->setAllowEmptyInnerHTML(true);
		if ($this->hasLabelString()) {
			$ls = $this->getLabelString();
			while ($ls instanceof ValueReturningCommandInterface) {
				$ls = $ls->evaluate();
			}
			$label->pushSuccessor($ls);
		}
		return $this->setLabelElement($label);
	}

	public function setLabelElement(?LabelElement $label): ?LabelElement{
		$f = __METHOD__; //FancyCheckbox::getShortClass()."(".static::getShortClass().")->setLabelElement()";
		if (! $label->hasAttributes()) {
			Debug::error("{$f} label does not have any attributes");
		} else {
			// Debug::print("{$f} returning normally");
		}
		return parent::setLabelElement($label);
	}

	protected function generateSuccessors(): ?array{
		$f = __METHOD__; //FancyCheckbox::getShortClass()."(".static::getShortClass().")->generateSuccessors()";
		$label = $this->getLabelElement();
		if ($label->getDeletedFlag()) {
			Debug::error("{$f} label was already deleted");
		} elseif (! $label->hasAttributes()) {
			Debug::error("{$f} label was not deleted but lacks any attributes whatsoever");
		} else {
			// Debug::print("{$f} returning normally");
		}
		if($this->hasStyleProperties()){
			$label->setStyleProperties($this->ejectStyleProperties());
		}
		return [
			$label
		];
	}
}
