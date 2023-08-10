<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterSetValueEvent extends SetValueEvent
{

	public function __construct($value, $properties = null)
	{
		parent::__construct(EVENT_AFTER_SET_VALUE, $value, $properties);
	}
}
