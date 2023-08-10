<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\map;

use JulianSeymour\PHPWebApplicationFramework\element\EmptyElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DownloadAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\image\AlternateTextAttributeTrait;

class AreaElement extends EmptyElement
{

	use AlternateTextAttributeTrait;
	use DownloadAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "area";
	}

	public function setCoordinatesAttribute($value)
	{
		return $this->setAttribute("coords", $value);
	}

	public function getCoordinatesAttribute()
	{
		return $this->getAttribute("coords");
	}

	public function hasCoordinatesAttribute()
	{
		return $this->hasAttribute("coords");
	}

	public function coordinates($value)
	{
		$this->setCoordinatesAttribute($value);
		return $this;
	}

	public function setShapeAttribute($value)
	{
		return $this->setAttribute("shape", $value);
	}

	public function getShapeAttribute()
	{
		return $this->getAttribute("shape");
	}

	public function hasShapeAttribute()
	{
		return $this->hasAttribute("shape");
	}

	public function shape($value)
	{
		$this->setShapeAttribute($value);
		return $this;
	}
}
