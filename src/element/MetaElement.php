<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\CharacterSetAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;

class MetaElement extends EmptyElement
{

	use CharacterSetAttributeTrait;
	use NameAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "meta";
	}

	public function getUri()
	{
		return static::getUriStatic();
	}

	public function hasContentAttribute()
	{
		return $this->hasAttribute("content");
	}

	public function hasHttpEquivAttribute()
	{
		return $this->hasAttribute("http-equiv");
	}

	public function getContentAttribute()
	{
		return $this->getAttribute("content");
	}

	public function getHttpEquivAttribute()
	{
		return $this->getAttribute("http-equiv");
	}

	public function setContentAttribute($content)
	{
		return $this->setAttribute("content", $content);
	}

	public function setHttpEquivAttribute($http_equiv)
	{
		return $this->setAttribute("http-equiv", $http_equiv);
	}
}
