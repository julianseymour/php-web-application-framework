<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterConstructorEvent extends Event{

	public function __construct($properties){
		parent::__construct(EVENT_AFTER_CONSTRUCTOR, $properties);
	}
}
