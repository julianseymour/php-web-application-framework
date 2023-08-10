<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\MediaAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\RelationshipAttributeTrait;

/**
 * AnchorElement, AreaElement and LinkElement
 *
 * @author j
 */
trait HypertextLanguageAttributeTrait
{

	use HypertextAttributeTrait;
	use MediaAttributeTrait;
	use RelationshipAttributeTrait;

	public function setHypertextLanguageAttribute($value)
	{
		return $this->setAttribute("hreflang", $value);
	}

	public function getHypertextLanguageAttribute()
	{
		return $this->getAttribute("hreflang");
	}

	public function hasHypertextLanguageAttribute()
	{
		return $this->hasAttribute("hreflang");
	}

	public function hypertextLanguage($value)
	{
		$this->setHypertextLanguageAttribute($value);
		return $this;
	}
}
