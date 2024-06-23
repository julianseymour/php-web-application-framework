<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterReplicateEvent extends Event{

	public function __construct($replica=null, $properties = null){
		if(!isset($properties) || !is_array($properties)){
			$properties = [];
		}
		if($replica !== null){
			$properties['replica'] = $replica;
		}
		parent::__construct(EVENT_AFTER_REPLICATE, $properties);
	}
}
