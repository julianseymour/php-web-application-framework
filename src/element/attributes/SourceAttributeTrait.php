<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait SourceAttributeTrait
{

	public function hasSourceAttribute(): bool
	{
		return $this->hasAttribute("src");
	}

	public function getSourceAttribute()
	{
		return $this->getAttribute("src");
	}

	public function setSourceAttribute($src)
	{
		return $this->setAttribute("src", $src);
	}
}