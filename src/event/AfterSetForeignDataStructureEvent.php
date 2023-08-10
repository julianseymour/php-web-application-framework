<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterSetForeignDataStructureEvent extends SetForeignDataStructureEvent
{

	public function __construct($columnName, $struct, $properties = null)
	{
		parent::__construct(EVENT_AFTER_SET_FOREIGN, $columnName, $struct, $properties);
	}
}
