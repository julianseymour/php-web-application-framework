<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\embed;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DimensionAttributesTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ReferrerPolicyAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;

class InlineFrameElement extends Element
{

	use DimensionAttributesTrait;
	use NameAttributeTrait;
	use ReferrerPolicyAttributeTrait;
	use SourceAttributeTrait;

	// XXX legacy (not deprecated): allowpayment,
	// XXX experimental: csp
	public function __construct($src)
	{
		parent::__construct(ALLOCATION_MODE_NEVER);
		$this->setSourceAttribute($src);
	}

	public static function getElementTagStatic(): string
	{
		return "iframe";
	}

	public function setLoadingAttribute($value)
	{ // experimental
		return $this->setAttribute("loading", $value);
	}

	public function getLoadingAttribute()
	{
		return $this->getAttribute("loading");
	}

	public function hasLoadingAttribute()
	{
		return $this->hasAttribute("loading");
	}

	public function setAllowFullscreenAttribute($value)
	{ // legacy
		return $this->setAttribute("allowfullscreen", $value);
	}

	public function hasAllowFullscreenAttribute()
	{
		return $this->hasAttribute("allowfullscreen");
	}

	public function getAllowFullscreenAttribute()
	{
		return $this->getAttribute("allowfullscreen");
	}

	public function allowFullscreen($value)
	{
		$this->setAllowFullscreenAttribute($value);
		return $this;
	}

	public function setAllowAttribute($value)
	{
		return $this->setAttribute("allow", $value);
	}

	public function hasAllowAttribute()
	{
		return $this->hasAttribute("allow");
	}

	public function getAllowAttribute()
	{
		return $this->getAttribute("allow");
	}

	public function allow($value)
	{
		$this->setAllowAttribute($value);
		return $this;
	}

	public function setSandboxAttribute($value)
	{
		return $this->setAttribute("sandbox", $value);
	}

	public function hasSandboxAttribute()
	{
		return $this->hasAttribute("sandbox");
	}

	public function getSandboxAttribute()
	{
		return $this->getAttribute("sandbox");
	}

	public function sandbox($value)
	{
		$this->setSandboxAttribute($value);
		return $this;
	}

	public function setSourceDocumentAttribute($value)
	{
		return $this->setAttribute("srcdoc", $value);
	}

	public function hasSourceDocumentAttribute()
	{
		return $this->hasAttribute("srcdoc");
	}

	public function getSourceDocumentAttribute()
	{
		return $this->getAttribute("srcdoc");
	}

	public function sourceDocument($value)
	{
		$this->setSourceDocumentAttribute($value);
		return $this;
	}
}
