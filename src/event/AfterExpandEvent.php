<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterExpandEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_EXPAND, $properties);
	}
}
