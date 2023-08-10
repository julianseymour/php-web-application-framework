<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeGenerateKeyEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_BEFORE_GENERATE_KEY, $properties);
	}
}
