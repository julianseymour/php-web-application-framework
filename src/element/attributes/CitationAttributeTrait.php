<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait CitationAttributeTrait
{

	public function setCitationAttribute($value)
	{
		return $this->setAttribute("cite", $value);
	}

	public function getCitationAttribute()
	{
		return $this->getAttribute("cite");
	}

	public function hasCitationAttribute(): bool
	{
		return $this->hasAttribute("cite");
	}

	public function cite($value)
	{
		$this->setCitationAttribute($value);
		return $this;
	}
}
