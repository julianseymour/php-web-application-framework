<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;

class ReleaseScopeEvent extends ReleaseEvent{
	
	public function __construct(?Scope $scope=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($scope !== null){
			$properties['scope'] = $scope;
		}
		parent::__construct(EVENT_RELEASE_SCOPE, $deallocate, $properties);
	}
}
