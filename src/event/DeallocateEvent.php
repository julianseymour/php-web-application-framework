<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class DeallocateEvent extends Event{
	
	public function __construct(?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		parent::__construct(EVENT_DEALLOCATE, $properties);
	}
}
