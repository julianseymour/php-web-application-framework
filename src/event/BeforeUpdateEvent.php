<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeUpdateEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_UPDATE, $properties);
	}
}
