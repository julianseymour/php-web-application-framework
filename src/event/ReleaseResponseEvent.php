<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;

class ReleaseResponseEvent extends ReleaseEvent{
	
	public function __construct(?XMLHttpResponse $response=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($response !== null){
			$properties['response'] = $response;
		}
		parent::__construct(EVENT_RELEASE_RESPONSE, $deallocate, $properties);
	}
}
