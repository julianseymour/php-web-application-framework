<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait TargetAttributeTrait
{

	public function setTargetAttribute($target)
	{
		return $this->setAttribute("target", $target);
	}

	public function getTargetAttribute()
	{
		return $this->getAttribute("target");
	}

	public function hasTargetAttribute(): bool
	{
		return $this->hasAttribute("target");
	}
}
