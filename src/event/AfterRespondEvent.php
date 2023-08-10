<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterRespondEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_RESPOND, $properties);
	}
}
