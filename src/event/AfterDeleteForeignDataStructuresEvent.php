<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterDeleteForeignDataStructuresEvent extends ForeignDataStructuresEvent
{

	public function __construct(string $when, ?array $properties = null)
	{
		parent::__construct(EVENT_AFTER_DELETE_FOREIGN, $when, $properties);
	}
}
