<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterExecuteEvent extends Event
{

	public function __construct($status, $properties = null)
	{
		if(!is_array($properties)) {
			$properties = [];
		}
		if($status !== null) {
			$properties['status'] = $status;
		}
		parent::__construct(EVENT_AFTER_EXECUTE, $properties);
	}
}
