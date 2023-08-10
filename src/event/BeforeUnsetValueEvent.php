<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeUnsetValueEvent extends UnsetValueEvent
{

	public function __construct($force = false, $properties = null)
	{
		parent::__construct(EVENT_BEFORE_UNSET_VALUE, $force);
	}
}
