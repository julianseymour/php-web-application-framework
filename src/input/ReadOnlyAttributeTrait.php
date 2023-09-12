<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ReadOnlyAttributeTrait
{

	public function setReadOnlyAttribute($value)
	{
		return $this->setAttribute("readonly", $value);
	}

	public function hasReadOnlyAttribute()
	{
		return $this->hasAttribute("readonly");
	}

	public function getReadOnlyAttribute()
	{
		$f = __METHOD__; //"ReadOnlyAttributeTrait(".static::getShortClass().")->getReadOnlyAttribute()";
		if(!$this->hasReadOnlyAttribute()) {
			Debug::error("{$f} read only attribute is undefined");
		}
		return $this->getAttribute("readonly");
	}
}
