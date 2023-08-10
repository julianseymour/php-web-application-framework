<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

trait AlternateTextAttributeTrait
{

	public function setAlternateTextAttribute($alt)
	{
		return $this->setAttribute("alt", $alt);
	}

	public function hasAlternateTextAttribute(): bool
	{
		return $this->hasAttribute("alt");
	}

	public function getAlternateTextAttribute()
	{
		return $this->getAttribute("alt");
	}
}
