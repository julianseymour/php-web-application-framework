<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use JulianSeymour\PHPWebApplicationFramework\event\Event;

class UseCaseTransitionEvent extends Event
{

	public function __construct($successor, $properties = null)
	{
		if (! is_array($properties)) {
			$properties = [];
		}
		if ($successor !== null) {
			$properties['successor'] = $successor;
		}
		parent::__construct(EVENT_USE_CASE_TRANSITION, $properties);
	}

	public function getSuccessor()
	{
		return $this->getProperty("successor");
	}
}
