<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeSetForeignDataStructureEvent extends SetForeignDataStructureEvent
{

	public function __construct($columnName, $struct, $properties = null)
	{
		parent::__construct(EVENT_BEFORE_SET_FOREIGN, $columnName, $struct, $properties);
	}
}