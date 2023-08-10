<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\datum\AbstractNumericDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\FloatingPointDatum;

abstract class NumericInput extends KeypadInput
{

	use ListAttributeTrait;

	public function hasStepAttribute()
	{
		return $this->hasAttribute("step");
	}

	public function getStepAttribute()
	{
		return $this->getAttribute("step");
	}

	public function setStepAttribute($step)
	{
		return $this->setAttribute("step", $step);
	}

	public function getMaximumAttribute()
	{
		return $this->getAttribute("max");
	}

	public function hasMaximumAttribute()
	{
		return $this->hasAttribute("max");
	}

	public function setMaximumAttribute($max)
	{
		return $this->setAttribute("max", $max);
	}

	public function getMinimumAttribute()
	{
		return $this->getAttribute("min");
	}

	public function hasMinimumAttribute()
	{
		return $this->hasAttribute("min");
	}

	public function setMinimumAttribute($min)
	{
		return $this->setAttribute("min", $min);
	}

	public function bindContext($context)
	{
		$f = __METHOD__; //NumericInput::getShortClass()."(".static::getShortClass().")->bindContext()";
		if ($context instanceof AbstractNumericDatum) {
			if ($context->hasMinimumValue()) {
				$this->setMinimumAttribute($context->getMinimumValue());
			}
			if ($context->hasMaximumValue()) {
				$this->setMaximumAttribute($context->getMaxmimumValue());
			}
			if ($context instanceof FloatingPointDatum) {
				if ($context->hasPrecision()) {
					$this->setStepAttribute($context->getPrecision());
				}
			}
		}
		return parent::bindContext($context);
	}
}
