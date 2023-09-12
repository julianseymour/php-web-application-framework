<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

class AfterSubindexEvent extends SubindexEvent
{

	public function __construct($index, $oldname, $properties = null)
	{
		if(! isset($properties) || ! is_array($properties)) {
			$properties = [];
		}
		$properties['oldName'] = $oldname;
		parent::__construct(EVENT_AFTER_SUBINDEX, $index, $properties);
	}
}
