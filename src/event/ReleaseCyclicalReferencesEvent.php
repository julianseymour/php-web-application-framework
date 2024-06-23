<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use function JulianSeymour\PHPWebApplicationFramework\debug;

class ReleaseCyclicalReferencesEvent extends ReleaseEvent{
	
	public function __construct(bool $deallocate = false, bool $force=false, $properties = null){
		if($properties === null){
			$properties = [];
		}
		$properties["force"] = $force;
		parent::__construct(EVENT_RELEASE_CYCLE, $deallocate, $properties);
	}
}