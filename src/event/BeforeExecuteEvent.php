<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeExecuteEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_EXECUTE, $properties);
	}
}
