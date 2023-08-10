<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterLoadEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_LOAD, $properties);
	}
}
