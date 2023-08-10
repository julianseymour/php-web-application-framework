<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeSaveEvent extends Event
{

	public function __construct(?array $properties = null)
	{
		parent::__construct(EVENT_BEFORE_SAVE, $properties);
	}
}
