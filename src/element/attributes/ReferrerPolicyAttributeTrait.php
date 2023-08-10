<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

trait ReferrerPolicyAttributeTrait
{

	public function setReferrerPolicyAttribute($value)
	{
		return $this->setAttribute("referrerpolicy", $value);
	}

	public function getReferrerPolicyAttribute()
	{
		return $this->getAttribute("referrerpolicy");
	}

	public function hasReferrerPolicy()
	{
		return $this->hasAttribute("referrerpolicy");
	}

	public function referrerPolicy($value)
	{
		$this->setReferrerPolicyAttribute($value);
		return $this;
	}
}
