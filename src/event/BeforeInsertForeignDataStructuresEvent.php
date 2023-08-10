<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeInsertForeignDataStructuresEvent extends ForeignDataStructuresEvent
{

	public function __construct(string $when, ?array $properties = null)
	{
		parent::__construct(EVENT_BEFORE_INSERT_FOREIGN, $when, $properties);
	}
}
