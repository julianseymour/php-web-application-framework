<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeUpdateForeignDataStructuresEvent extends EditForeignDataStructuresEvent{

	public function __construct(string $when, ?array $properties = null){
		parent::__construct(EVENT_BEFORE_UPDATE_FOREIGN, $when, $properties);
	}
}
