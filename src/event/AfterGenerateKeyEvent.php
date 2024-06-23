<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterGenerateKeyEvent extends Event
{

	public function __construct($key, $properties = null)
	{
		if(!isset($properties) || !is_array($properties)){
			$properties = [];
		}
		$properties['uniqueKey'] = $key;
		parent::__construct(EVENT_AFTER_GENERATE_KEY, $properties);
	}
}
