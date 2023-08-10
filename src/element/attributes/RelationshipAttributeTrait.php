<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait RelationshipAttributeTrait
{

	public function hasRelationshipAttribute(): bool
	{
		return $this->hasAttribute("rel");
	}

	public function getRelationshipAttribute()
	{
		return $this->getAttribute("rel");
	}

	public function setRelationshipAttribute($rel)
	{
		return $this->setAttribute("rel", $rel);
	}
}
