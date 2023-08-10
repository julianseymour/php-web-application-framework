<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait MediaAttributeTrait
{

	public function setMediaAttribute($value)
	{
		return $this->setAttribute("media", $value);
	}

	public function getMediaAttribute()
	{
		return $this->getAttribute("media");
	}

	public function hasMediaAttribute(): bool
	{
		return $this->hasAttribute("media");
	}

	public function media($value)
	{
		$this->setMediaAttribute($value);
		return $this;
	}
}
