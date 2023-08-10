<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

abstract class UnsetValueEvent extends Event
{

	public function __construct($type, $force = false, $properties = null)
	{
		if ($force && is_array($properties) && ! empty($properties)) {
			$properties['force'] = true;
		}
		parent::__construct($type, $properties);
	}
}
