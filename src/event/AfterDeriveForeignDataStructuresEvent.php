<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterDeriveForeignDataStructuresEvent extends Event{

	public function __construct($properties = null){
		parent::__construct(EVENT_AFTER_DERIVE, $properties);
	}
}
