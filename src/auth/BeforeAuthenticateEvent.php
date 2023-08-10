<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth;

use JulianSeymour\PHPWebApplicationFramework\event\Event;

class BeforeAuthenticateEvent extends Event
{

	public function __construct(?array $properties = null)
	{
		parent::__construct(EVENT_BEFORE_AUTHENTICATE, $properties);
	}
}
