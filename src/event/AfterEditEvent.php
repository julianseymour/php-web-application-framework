<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterEditEvent extends AbstractDirectiveEvent{

	public function __construct(?string $directive=null, ?array $properties = null){
		parent::__construct(EVENT_AFTER_EDIT, $directive, $properties);
	}
}
