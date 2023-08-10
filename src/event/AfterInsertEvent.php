<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterInsertEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_INSERT, $properties);
	}
}
