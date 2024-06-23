<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class AfterSetForeignDataStructureEvent extends AbstractForeignDataStructureEvent{

	public function __construct(?string $columnName=null, ?DataStructure $struct=null, ?array $properties = null){
		parent::__construct(EVENT_AFTER_SET_FOREIGN, $columnName, $struct, $properties);
	}
}
