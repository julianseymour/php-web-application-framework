<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeSetValueEvent extends SetValueEvent
{

	public function __construct($value, $properties = null)
	{
		parent::__construct(EVENT_BEFORE_SET_VALUE, $value, $properties);
	}
}
