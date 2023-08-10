<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class SubmitEvent extends Event
{

	public function __construct($properties = null)
	{
		parent::__construct(EVENT_SUBMIT_FORM, $properties);
	}
}
