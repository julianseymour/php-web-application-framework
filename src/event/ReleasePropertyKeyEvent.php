<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class ReleasePropertyKeyEvent extends ReleaseEvent{
	
	public function __construct(?string $propertyName=null, $key=null, $value=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($propertyName !== null){
			$properties['propertyName'] = $propertyName;
		}
		if($key !== null){
			$properties['key'] = $key;
		}
		if($value !== null){
			$properties['value'] = $value;
		}
		parent::__construct(EVENT_RELEASE_PROPERTY_KEY, $deallocate, $properties);
	}
}
