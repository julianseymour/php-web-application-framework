<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class BeforeSetForeignDataStructureEvent extends AbstractForeignDataStructureEvent{

	public function __construct(?string $columnName=null, ?DataStructure $struct=null, ?array $properties = null){
		parent::__construct(EVENT_BEFORE_SET_FOREIGN, $columnName, $struct, $properties);
	}
}