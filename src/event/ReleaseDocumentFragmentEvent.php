<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\script\DocumentFragment;

class ReleaseDocumentFragmentEvent extends ReleaseEvent{
	
	public function __construct(?DocumentFragment $fragment=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($fragment !== null){
			$properties['documentFragment'] = $fragment;
		}
		parent::__construct(EVENT_RELEASE_DOCUMENT_FRAGMENT, $deallocate, $properties);
	}
}
