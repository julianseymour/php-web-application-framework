<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterReplicateEvent extends Event
{

	public function __construct($replica, $properties = null)
	{
		if (! isset($properties) || ! is_array($properties)) {
			$properties = [];
		}
		$properties['replica'] = $replica;
		parent::__construct(EVENT_AFTER_REPLICATE, $properties);
	}
}
