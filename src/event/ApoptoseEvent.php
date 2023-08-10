<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class ApoptoseEvent extends Event
{

	public function __construct($caller, $properties = null)
	{
		if (! isset($properties) || ! is_array($properties)) {
			$properties = [];
		}
		$properties['caller'] = $caller;
		parent::__construct(EVENT_APOPTOSE, $properties);
	}
}
