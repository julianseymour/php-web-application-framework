<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeCreateTableEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_CREATE_TABLE, $properties);
	}
}
