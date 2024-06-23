<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;

class ReleaseLabelEvent extends ReleaseEvent{
	
	public function __construct(?LabelElement $label=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($label !== null){
			$properties['label'] = $label;
		}
		parent::__construct(EVENT_RELEASE_LABEL, $deallocate, $properties);
	}
}
