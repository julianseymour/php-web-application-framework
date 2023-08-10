<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

abstract class CitationalElement extends Element
{

	public function setCitationAttribute($value)
	{
		return $this->setAttribute("cite", $value);
	}

	public function getCitationAttribute()
	{
		return $this->getAttribute("cite");
	}

	public function hasCitationAttribute()
	{
		return $this->hasAttribute("cite");
	}

	public function cite($value)
	{
		$this->setCitationAttribute($value);
		return $this;
	}
}
