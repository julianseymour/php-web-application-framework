<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait TypeAttributeTrait
{

	public function setTypeAttribute($type)
	{
		return $this->setAttribute("type", $type);
	}

	public function hasTypeAttribute(): bool
	{
		return $this->hasAttribute("type");
	}

	public function getTypeAttribute(): string
	{
		return $this->getAttribute("type");
	}

	public function withTypeAttribute($type)
	{
		$this->setTypeAttribute($type);
		return $this;
	}
}
