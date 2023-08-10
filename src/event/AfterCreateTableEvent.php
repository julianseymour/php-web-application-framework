<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterCreateTableEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_CREATE_TABLE, $properties);
	}
}
