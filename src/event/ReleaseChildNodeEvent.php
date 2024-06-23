<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use function JulianSeymour\PHPWebApplicationFramework\debug;

class ReleaseChildNodeEvent extends ReleaseEvent{
	
	public function __construct($key = null, $child=null, bool $deallocate = false, $properties = null){
		if($properties === null){
			$properties = [];
		}
		if($key !== null){
			$properties['key'] = $key;
		}
		if($child !== null){
			$properties['childNode'] = $child;
		}
		parent::__construct(EVENT_RELEASE_CHILD, $deallocate, $properties);
	}
}
