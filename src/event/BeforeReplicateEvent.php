<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeReplicateEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_REPLICATE, $properties);
	}
}