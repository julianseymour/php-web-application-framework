<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

abstract class SetValueEvent extends Event
{

	public function __construct($type, $value, $properties = null)
	{
		if(! isset($properties) || ! is_array($properties)) {
			$properties = [];
		}
		$properties['value'] = $value;
		parent::__construct($type, $properties);
	}

	public function getValue()
	{
		return $this->getProperty("value");
	}
}
