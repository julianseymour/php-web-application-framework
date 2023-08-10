<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\LabelAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;

class TrackElement extends Element
{

	use LabelAttributeTrait;
	use SourceAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "track";
	}

	public function setDefaultAttribute($value = "")
	{
		return $this->setAttribute("default", $value);
	}

	public function hasDefaultAttribute()
	{
		return $this->hasAttribute("default");
	}

	public function getDefaultAttribute()
	{
		return $this->getAttribute("default");
	}

	public function default()
	{
		$this->setDefaultAttribute("");
		return $this;
	}

	public function setKindAttribute($value)
	{
		return $this->setAttribute("kind", $value);
	}

	public function getKindAttribute()
	{
		return $this->getAttribute("kind");
	}

	public function hasKindAttribute()
	{
		return $this->hasAttribute("kind");
	}

	public function kind($value)
	{
		$this->setKindAttribute($value);
		return $this;
	}

	public function setSourceLanguageAttribute($value)
	{
		return $this->setAttribute("srclang", $value);
	}

	public function getSourceLanguageAttribute()
	{
		return $this->getAttribute("srclang");
	}

	public function hasSourceLanguageAttribute()
	{
		return $this->hasAttribute("srclang");
	}

	public function sourceLanguage($value)
	{
		$this->setSourceLanguageAttribute($value);
		return $this;
	}
}
