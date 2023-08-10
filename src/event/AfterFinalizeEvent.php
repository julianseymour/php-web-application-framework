<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterRenderEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_AFTER_RENDER, $properties);
	}
}