<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeDeleteEvent extends Event{

	public function __construct($properties = null){
		parent::__construct(EVENT_BEFORE_DELETE, $properties);
	}
}