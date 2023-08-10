<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeEditEvent extends Event
{

	public function __construct(?array $properties = null)
	{
		parent::__construct(EVENT_BEFORE_EDIT, $properties);
	}
}
