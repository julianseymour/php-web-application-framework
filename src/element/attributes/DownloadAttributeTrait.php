<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\element\inline\HypertextLanguageAttributeTrait;

/**
 * AnchorElement and AreaElement
 *
 * @author j
 *        
 */
trait DownloadAttributeTrait
{

	use HypertextLanguageAttributeTrait;
	use ReferrerPolicyAttributeTrait;
	use TargetAttributeTrait;

	public function setDownloadAttribute($value)
	{
		return $this->setAttribute("download", $value);
	}

	public function hasDownloadAttribute()
	{
		return $this->hasAttribute("download");
	}

	public function getDownloadAttribute()
	{
		return $this->getAttribute("download");
	}

	public function download($value)
	{
		$this->setDownloadAttribute($value);
		return $this;
	}

	public function setPingAttribute($value)
	{
		return $this->setAttribute("ping", $value);
	}

	public function hasPingAttribute()
	{
		return $this->hasAttribute("ping");
	}

	public function getPingAttribute()
	{
		return $this->getAttribute("ping");
	}

	public function ping($value)
	{
		$this->setPingAttribute($value);
		return $this;
	}
}
