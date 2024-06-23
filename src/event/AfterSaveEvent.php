<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterSaveEvent extends AbstractDirectiveEvent{

	public function __construct(?string $directive=null, ?array $properties = null){
		parent::__construct(EVENT_AFTER_SAVE, $directive, $properties);
	}
}
