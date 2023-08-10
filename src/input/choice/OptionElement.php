<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\ValuedElement;
use JulianSeymour\PHPWebApplicationFramework\input\DisabledAttributeTrait;

class OptionElement extends ValuedElement
{

	use DisabledAttributeTrait;

	public function setSelectedAttribute($selected)
	{
		return $this->setAttribute("selected", "selected");
	}

	public function getSelectedAttribute()
	{
		return $this->getAttribute("selected");
	}

	public function hasSelectedAttribute()
	{
		return $this->hasAttribute("selected");
	}

	public function bindContext($context)
	{
		if (! $context->isUninitialized()) {
			$this->setValueAttribute($context->getSelectOptionValue());
		}
		return parent::bindContext($context);
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //OptionElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		if ($this->hasInnerHTML()) {
			return [
				$this->getInnerHTML()
			];
		} elseif (! $this->hasContext()) {
			Debug::warning("{$f} context is undefined");
			$this->debugPrintRootElement();
		}
		$context = $this->getContext();
		$innerHTML = $context->getSelectOptionInnerHTML();
		$this->setInnerHTML($innerHTML);
		return [
			$innerHTML
		];
	}

	public static function getElementTagStatic(): string
	{
		return "option";
	}

	public function select()
	{
		$this->setSelectedAttribute("selected");
		return $this;
	}

	public function deselect()
	{
		$this->removeAttribute("selected");
		return $this;
	}
}
