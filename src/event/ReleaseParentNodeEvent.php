<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class ReleaseParentNodeEvent extends ReleaseEvent{
	
	public function __construct($parent=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($parent !== null){
			$properties['parentNode'] = $parent;
		}
		parent::__construct(EVENT_RELEASE_PARENT, $deallocate, $properties);
	}
}
