<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait AutocompleteAttributeTrait
{

	public function setAutocompleteAttribute($attr)
	{
		return $this->setAttribute("autocomplete", $attr);
	}

	public function hasAutocompleteAttribute(): bool
	{
		return $this->hasAttribute("autocomplete");
	}

	public function getAutocompleteAttribute()
	{
		return $this->getAttribute("autocomplete");
	}
}