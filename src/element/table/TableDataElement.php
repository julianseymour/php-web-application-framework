<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\table;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

class TableDataElement extends Element
{

	public static function getElementTagStatic(): string
	{
		return "td";
	}

	public function setColumnSpanAttribute($value)
	{
		return $this->setAttribute("colspan", $value);
	}

	public function hasColumnSpanAttribute()
	{
		return $this->hasAttribute("colspan");
	}

	public function getColumnSpanAttribute()
	{
		return $this->getAttribute("colspan");
	}

	public function removeColumnSpanAttribute()
	{
		return $this->removeAttribute("colspan");
	}

	public function setRowSpanAttribute($value)
	{
		return $this->setAttribute("rowspan", $value);
	}

	public function hasRowSpanAttribute()
	{
		return $this->hasAttribute("rowspan");
	}

	public function getRowSpanAttribute()
	{
		return $this->getAttribute("rowspan");
	}

	public function removeRowSpanAttribute()
	{
		return $this->removeAttribute("rowspan");
	}

	public function setAbbreviationAttribute($value)
	{
		return $this->setAttribute("abbr", $value);
	}

	public function hasAbbreviationAttribute()
	{
		return $this->hasAttribute("abbr");
	}

	public function getAbbreviationAttribute()
	{
		return $this->getAttribute("abbr");
	}

	public function removeAbbreviationAttribute()
	{
		return $this->removeAttribute("abbr");
	}

	public function setHeadersAttribute($value)
	{
		return $this->setAttribute("headers", $value);
	}

	public function hasHeadersAttribute()
	{
		return $this->hasAttribute("headers");
	}

	public function getHeadersAttribute()
	{
		return $this->getAttribute("headers");
	}

	public function removeHeadersAttribute()
	{
		return $this->removeAttribute("headers");
	}
}
