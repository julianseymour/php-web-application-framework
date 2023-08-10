<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait CharacterSetAttributeTrait
{

	public function hasCharacterSetAttribute(): bool
	{
		return $this->hasAttribute("charset");
	}

	public function getCharacterSetAttribute()
	{
		return $this->getAttribute("charset");
	}

	public function setCharacterSetAttribute($charset)
	{
		return $this->setAttribute("charset", $charset);
	}

	public function characterSet($value)
	{
		$this->setCharacterSsetAttribute($value);
		return $this;
	}
}
