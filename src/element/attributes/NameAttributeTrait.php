<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputNameCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait NameAttributeTrait
{

	public function hasNameAttribute(): bool
	{
		return $this->hasAttribute("name");
	}

	public function getNameAttribute()
	{
		$f = __METHOD__; //"NameAttributeTrait(".static::getShortClass().")->getNameAttribute()";
		if (! $this->hasNameAttribute()) {
			Debug::error("{$f} name attribute is undefined");
		}
		return $this->getAttribute("name");
	}

	public function setNameAttribute($name)
	{
		$f = __METHOD__; //"NameAttributeTrait(".static::getShortClass().")->setNameAttribute()";
		$print = false;
		if ($print) {
			Debug::print("{$f} setting name attribute to \"{$name}\"");
		}
		return $this->setAttribute("name", $name);
	}

	public function withNameAttribute($name)
	{
		$this->setNameAttribute($name);
		return $this;
	}

	public function setNameAttributeCommand($name): SetInputNameCommand
	{
		return new SetInputNameCommand($this, $name);
	}
}
