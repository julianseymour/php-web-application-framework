<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use function JulianSeymour\PHPWebApplicationFramework\debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class ReleaseForeignDataStructureEvent extends AbstractForeignDataStructureEvent{
	
	public function __construct(?string $columnName=null, ?DataStructure $struct=null, $foreignKey=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($foreignKey !== null){
			$properties['foreignKey'] = $foreignKey;
		}
		$properties['recursive'] = $deallocate;
		parent::__construct(EVENT_RELEASE_FOREIGN, $columnName, $struct, $properties);
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
