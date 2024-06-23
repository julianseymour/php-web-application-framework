<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeSaveEvent extends AbstractDirectiveEvent{

	public function __construct(?string $directive=null, ?array $properties = null){
		parent::__construct(EVENT_BEFORE_SAVE, $directive, $properties);
	}
}
