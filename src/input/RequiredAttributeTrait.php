<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait RequiredAttributeTrait
{

	public function hasRequiredAttribute()
	{
		return $this->hasAttribute("required");
	}

	public function getRequiredAttribute()
	{
		$f = __METHOD__; //"RequiredAttributeTrait(".static::getShortClass().")->getRequiredAttribute()";
		if (! $this->hasRequiredAttribute()) {
			Debug::error("{$f} required attribute is undefined");
		}
		return $this->getAttribute("required");
	}

	public function setRequiredAttribute($attr)
	{
		return $this->setAttribute("required", $attr);
	}
	
	public function require($value="required"){
		$this->setRequiredAttribute($value);
		return $this;
	}
}
