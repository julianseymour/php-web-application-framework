<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ReleaseUseCasePredecessorEvent extends ReleaseEvent{
	
	public function __construct(?UseCase $predecessor=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($predecessor !== null){
			$properties['predecessor'] = $predecessor;
		}
		parent::__construct(EVENT_RELEASE_USE_CASE_PREDECESSOR, $deallocate, $properties);
	}
}
