<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

class ReleaseFormEvent extends ReleaseEvent{
	
	public function __construct(?AjaxForm $form=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($form !== null){
			$properties['form'] = $form;
		}
		parent::__construct(EVENT_RELEASE_FORM, $deallocate, $properties);
	}
}
