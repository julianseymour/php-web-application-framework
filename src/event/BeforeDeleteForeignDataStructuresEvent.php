<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeDeleteForeignDataStructuresEvent extends ForeignDataStructuresEvent
{

	public function __construct(string $when, ?array $properties = null)
	{
		parent::__construct(EVENT_BEFORE_DELETE_FOREIGN, $when, $properties);
	}
}
