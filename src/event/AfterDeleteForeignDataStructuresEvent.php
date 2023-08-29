<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterDeleteForeignDataStructuresEvent extends ForeignDataStructuresEvent{

	public function __construct(?array $properties = null){
		parent::__construct(EVENT_AFTER_DELETE_FOREIGN, CONST_AFTER, $properties);
	}
}
