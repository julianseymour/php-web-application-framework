<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeExpandEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_EXPAND, $properties);
	}
}