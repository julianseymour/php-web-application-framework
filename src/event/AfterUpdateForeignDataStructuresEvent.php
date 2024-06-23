<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterUpdateForeignDataStructuresEvent extends EditForeignDataStructuresEvent{

	public function __construct(string $when, ?array $properties = null){
		parent::__construct(EVENT_AFTER_UPDATE_FOREIGN, $when, $properties);
	}
}
