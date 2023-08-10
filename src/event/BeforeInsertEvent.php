<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeInsertEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_INSERT, $properties);
	}
}
