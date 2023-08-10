<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeDeriveForeignDataStructuresEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_DERIVE, $properties);
	}
}
