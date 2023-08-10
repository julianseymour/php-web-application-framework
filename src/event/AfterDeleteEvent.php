<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterDeleteEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_DELETE, $properties);
	}
}
