<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\table;

trait SpanAttributeTrait
{

	public function hasSpanAttribute()
	{
		return $this->hasAttribute("span");
	}

	public function getSpanAttribute()
	{
		return $this->getAttribute("span");
	}

	public function setSpanAttribute($value)
	{
		return $this->setAttribute("span", $value);
	}
}
