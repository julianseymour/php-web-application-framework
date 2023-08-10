<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeLoadEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_LOAD, $properties);
	}
}
