<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeRespondEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_RESPOND, $properties);
	}
}
