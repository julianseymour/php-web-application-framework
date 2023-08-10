<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterUnsetValueEvent extends UnsetValueEvent
{

	public function __construct($force = false, $properties = null)
	{
		parent::__construct(EVENT_AFTER_UNSET_VALUE, $force);
	}
}
