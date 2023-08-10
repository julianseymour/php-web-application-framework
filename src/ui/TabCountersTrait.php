<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

trait TabCountersTrait
{

	protected $tabCounters;

	public function setTabCounters($keyvalues)
	{
		return $this->tabCounters = $keyvalues;
	}

	public function getTabcounters()
	{
		return $this->tabCounters;
	}
}
