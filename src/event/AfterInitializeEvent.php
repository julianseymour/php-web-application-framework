<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterInitializeEvent extends Event{
	
	public function __construct(?array $properties=null){
		parent::__construct(EVENT_After_INITIALIZE, $properties);
	}
}
