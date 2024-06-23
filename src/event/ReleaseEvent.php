<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use function JulianSeymour\PHPWebApplicationFramework\debug;

abstract class ReleaseEvent extends Event{
	
	public function __construct(?string $event_type=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		$properties["recursive"] = $deallocate;
		parent::__construct($event_type, $properties);
	}
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasTarget()){
			unset($this->target);
		}
		unset($this->listenerId);
		unset($this->eventType);
		unset($this->properties);
		unset($this->propertyTypes);
		if(
			$deallocate &&
			DEBUG_MODE_ENABLED &&
			DEBUG_REFERENCE_MAPPING_ENABLED &&
			$this->hasDebugId() &&
			debug()->has($this->debugId)
		){
			debug()->remove($this->debugId);
		}
	}
}