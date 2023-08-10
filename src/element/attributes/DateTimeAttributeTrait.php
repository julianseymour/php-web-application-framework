<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait DateTimeAttributeTrait
{

	public function setDateTimeAttribute($value)
	{
		return $this->setAttribute("datetime", $value);
	}

	public function getDateTimeAttribute()
	{
		return $this->getAttribute("datetime");
	}

	public function hasDateTimeAttribute(): bool
	{
		return $this->hasAttribute("datetime");
	}

	public function dateTime($value)
	{
		$this->setDateTimeAttribute($value);
		return $this;
	}
}
