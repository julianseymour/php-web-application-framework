<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterInsertForeignDataStructuresEvent extends EditForeignDataStructuresEvent{

	public function __construct(string $when, ?array $properties = null){
		parent::__construct(EVENT_AFTER_INSERT_FOREIGN, $when, $properties);
	}
}
