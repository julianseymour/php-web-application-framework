<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

abstract class SubindexEvent extends Event
{

	public function __construct($type, $index, $properties = null)
	{
		if(!isset($properties) || !is_array($properties)){
			$properties = [];
		}
		$properties['index'] = $index;
		parent::__construct($type, $properties);
	}
}
