<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterUpdateEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_UPDATE, $properties);
	}
}
