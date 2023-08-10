<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeConstructorEvent extends Event
{

	public function __construct($properties)
	{
		parent::__construct(EVENT_BEFORE_CONSTRUCTOR, $properties);
	}
}
