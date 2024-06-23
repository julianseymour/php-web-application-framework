<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeReleaseAllForeignDataStructuresEvent extends Event{
	
	public function __construct(bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		$properties['recursive'] = $deallocate;
		parent::__construct(EVENT_BEFORE_RELEASE_ALL_FOREIGN, $properties);
	}
}
