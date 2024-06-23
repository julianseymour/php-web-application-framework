<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class ReleaseInputEvent extends ReleaseEvent{
	
	public function __construct(?string $name=null, $input=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($name !== null){
			$properties['name'] = $name;
		}
		if($input !== null){
			$properties['input'] = $input;
		}
		parent::__construct(EVENT_RELEASE_INPUT, $deallocate, $properties);
	}
}
