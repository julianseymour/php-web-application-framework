<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterEjectValueEvent extends Event
{

	public function __construct($value, $properties = null)
	{
		if(!is_array($properties)) {
			$properties = [
				'value' => $value
			];
		}else{
			$properties['value'] = $value;
		}
		parent::__construct(EVENT_AFTER_EJECT, $properties);
	}
}
