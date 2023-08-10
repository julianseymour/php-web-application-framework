<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterUpdateForeignDataStructuresEvent extends ForeignDataStructuresEvent
{

	public function __construct(string $when, ?array $properties = null)
	{
		parent::__construct(EVENT_AFTER_UPDATE_FOREIGN, $when, $properties);
	}
}
