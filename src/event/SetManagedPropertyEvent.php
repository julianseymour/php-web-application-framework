<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class SetManagedPropertyEvent extends Event{
	
	public function __construct($key=null, $value=null, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($key !== null){
			$properties['key'] = $key;
		}
		if($value !== null){
			$properties['value'] = $value;
		}
		parent::__construct(EVENT_SET_PROPERTY, $properties);
	}
}
