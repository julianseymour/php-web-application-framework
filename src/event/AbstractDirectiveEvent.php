<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

abstract class AbstractDirectiveEvent extends Event{
	
	public function __construct(?string $event_type=null, ?string $directive=null, ?array $properties=null){
		if($directive !== null){
			if($properties === null){
				$properties = [];
			}
			$properties['directive'] = $directive;
		}
		parent::__construct($event_type, $properties);
	}
}