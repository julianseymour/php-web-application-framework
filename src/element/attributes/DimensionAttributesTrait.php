<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DimensionAttributesTrait
{

	public function setHeightAttribute($h)
	{
		return $this->setAttribute("height", $h);
	}

	public function hasHeightAttribute()
	{
		return $this->hasAttribute("height");
	}

	public function getHeightAttribute()
	{
		$f = __METHOD__; //"DimensionAttributesTrait(".static::getShortClass().")->getHeightAttribute()";
		if(!$this->hasHeightAttribute()){
			Debug::error("{$f} height attribute is undefined");
		}
		return $this->getAttribute("height");
	}

	public function setWidthAttribute($w)
	{
		return $this->setAttribute("width", $w);
	}

	public function hasWidthAttribute()
	{
		return $this->hasAttribute("width");
	}

	public function getWidthAttribute()
	{
		$f = __METHOD__; //"DimensionAttributesTrait(".static::getShortClass().")->getWidthAttribute()";
		if(!$this->hasWidthAttribute()){
			Debug::error("{$f} width attribute is undefined");
		}
		return $this->getAttribute("width");
	}
}
