<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DisabledAttributeTrait
{

	public function setDisabledAttribute($value)
	{
		return $this->setAttribute("disabled", $value);
	}

	public function hasDisabledAttribute()
	{
		return $this->hasAttribute("disabled");
	}

	public function getDisabledAttribute()
	{
		$f = __METHOD__; //"DisabledAttributeTrait(".static::getShortClass().")->getDisabledAttribute()";
		if(!$this->hasDisabledAttribute()) {
			Debug::error("{$f} disabled attribute is undefined");
		}
		return $this->getAttribute("disabled");
	}

	public function disable()
	{
		$this->setDisabledAttribute("disabled");
		return $this;
	}
}
