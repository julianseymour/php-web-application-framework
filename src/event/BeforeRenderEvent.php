<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class BeforeRenderEvent extends Event
{

	public function __construct(?array $properties = null)
	{
		parent::__construct(EVENT_BEFORE_RENDER, $properties);
	}
}
