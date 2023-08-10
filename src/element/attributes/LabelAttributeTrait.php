<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait LabelAttributeTrait
{

	public function setLabelAttribute($value)
	{
		return $this->setAttribute("label", $value);
	}

	public function getLabelAttribute()
	{
		return $this->getAttribute("label");
	}

	public function hasLabelAttribute(): bool
	{
		return $this->hasAttribute("label");
	}
}
