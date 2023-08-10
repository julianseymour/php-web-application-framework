<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class QuotaDatum extends UnsignedIntegerDatum
{

	protected $intervalSeconds;

	public function hasIntervalSeconds()
	{
		return isset($this->interfaceElement);
	}

	public function getIntervalSeconds()
	{
		if ($this->hasIntervalSeconds()) {
			return null;
		}
		return $this->intervalSeconds;
	}

	public function setIntervalSeconds($seconds)
	{
		return $this->intervalSeconds = $seconds;
	}
}
