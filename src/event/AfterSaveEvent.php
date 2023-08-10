<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterSaveEvent extends Event
{

	public function __construct(?array $properties = null)
	{
		parent::__construct(EVENT_AFTER_SAVE, $properties);
	}
}
