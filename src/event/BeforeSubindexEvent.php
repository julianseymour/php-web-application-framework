<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeSubindexEvent extends SubindexEvent
{

	public function __construct($index, $properties = null)
	{
		parent::__construct(EVENT_BEFORE_SUBINDEX, $index, $properties);
	}
}
