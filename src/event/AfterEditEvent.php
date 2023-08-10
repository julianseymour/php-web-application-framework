<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterEditEvent extends Event
{

	public function __construct(?array $properties = null)
	{
		parent::__construct(EVENT_AFTER_EDIT, $properties);
	}
}
