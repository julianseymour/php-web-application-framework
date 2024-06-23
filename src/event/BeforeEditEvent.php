<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeEditEvent extends AbstractDirectiveEvent{

	public function __construct(?string $directive=null, ?array $properties = null){
		parent::__construct(EVENT_BEFORE_EDIT, $directive, $properties);
	}
}
