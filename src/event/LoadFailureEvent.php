<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class LoadFailureEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_LOAD_FAILED, $properties);
	}
}
