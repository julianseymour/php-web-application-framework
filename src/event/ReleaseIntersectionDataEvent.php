<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;

class ReleaseIntersectionDataEvent extends ReleaseEvent{
	
	public function __construct(?IntersectionData $intersection=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($intersection !== null){
			$properties['intersectionData'] = $intersection;
		}
		parent::__construct(EVENT_RELEASE_INTERSECTION, $deallocate, $properties);
	}
}
